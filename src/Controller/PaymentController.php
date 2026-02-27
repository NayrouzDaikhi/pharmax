<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Payment;
use App\Repository\CommandeRepository;
use App\Service\EmailService;
use App\Service\InvoiceService;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Psr\Log\LoggerInterface;

#[Route('/payment')]
class PaymentController extends AbstractController
{
    public function __construct(
        private StripeService $stripeService,
        private InvoiceService $invoiceService,
        private EmailService $emailService,
        private EntityManagerInterface $entityManager,
        private CommandeRepository $commandeRepository,
        private LoggerInterface $logger,
    ) {
    }

    /**
     * Initiate payment checkout
     */
    #[Route('/checkout/{id}', name: 'app_payment_checkout', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function checkout(?Commande $commande, Request $request): Response
    {
        if (!$commande) {
            throw $this->createNotFoundException('Order not found');
        }

        // Only allow payment for pending or unpaid orders
        if (!in_array($commande->getStatut(), ['en_attente', 'en_cours'])) {
            $this->addFlash('error', 'This order cannot be paid at this time');
            return $this->redirectToRoute('app_frontend_commande_show', ['id' => $commande->getId()]);
        }

        try {
            // Prepare order items for Stripe
            $orderData = [
                'order_id' => $commande->getId(),
                'customer_name' => $commande->getUtilisateur()->getFirstName() . ' ' . $commande->getUtilisateur()->getLastName(),
                'customer_email' => $commande->getUtilisateur()->getEmail(),
                'items' => [],
            ];

            foreach ($commande->getLigneCommandes() as $ligne) {
                $orderData['items'][] = [
                    'name' => $ligne->getNomProduit(),
                    'description' => 'SKU: ' . $ligne->getSkuProduit(),
                    'price' => $ligne->getPrix(),
                    'quantity' => $ligne->getQuantite(),
                ];
            }

            // Create Stripe checkout session
            $session = $this->stripeService->createCheckoutSession(
                $orderData,
                $this->generateUrl('app_payment_success', [], \Symfony\Component\Routing\UrlGeneratorInterface::ABSOLUTE_URL),
                $this->generateUrl('app_payment_cancel', [], \Symfony\Component\Routing\UrlGeneratorInterface::ABSOLUTE_URL)
            );

            return $this->redirect($session->url, 303);
        } catch (\Exception $e) {
            $this->logger->error('Stripe checkout error: ' . $e->getMessage());
            $this->addFlash('error', 'Payment initialization failed. Please try again.');
            return $this->redirectToRoute('app_frontend_commande_show', ['id' => $commande->getId()]);
        }
    }

    /**
     * Payment success callback
     */
    #[Route('/success', name: 'app_payment_success', methods: ['GET'])]
    public function success(Request $request): Response
    {
        $sessionId = $request->query->get('session_id');

        if (!$sessionId) {
            $this->addFlash('error', 'Invalid session');
            return $this->redirectToRoute('app_frontend_commande_index');
        }

        try {
            $session = \Stripe\Checkout\Session::retrieve($sessionId);
            $orderId = $session->metadata['order_id'] ?? null;

            if (!$orderId) {
                throw new \Exception('Order ID not found in session');
            }

            $commande = $this->commandeRepository->find($orderId);
            if (!$commande) {
                throw $this->createNotFoundException('Order not found');
            }

            // Update order status to paid
            $commande->setStatut('payee');

            // Record payment
            $payment = new Payment();
            $payment->setCommande($commande);
            $payment->setMontant($commande->getTotales());
            $payment->setStatut('succeeded');
            $payment->setMethodePaiement('stripe');
            $payment->setStripeSessionId($sessionId);
            $payment->setStripePaymentIntentId($session->payment_intent ?? null);
            $payment->setDatePaiement(new \DateTime());

            $this->entityManager->persist($payment);
            $this->entityManager->flush();

            // Send confirmation email with invoice
            $invoiceHtml = $this->invoiceService->generateInvoiceHtml($commande, $session->payment_intent);
            $this->emailService->sendEmail(
                $commande->getUtilisateur()->getEmail(),
                'Payment Confirmation - Invoice',
                'payment/confirmation',
                [
                    'commande' => $commande,
                    'invoice' => $invoiceHtml,
                    'paymentId' => $session->payment_intent,
                ]
            );

            $this->addFlash('success', 'Payment successful! Your invoice has been sent to your email.');
            return $this->redirectToRoute('app_frontend_commande_show', ['id' => $commande->getId()]);
        } catch (\Exception $e) {
            $this->logger->error('Payment success handling error: ' . $e->getMessage());
            $this->addFlash('error', 'Error processing payment confirmation');
            return $this->redirectToRoute('app_frontend_commande_index');
        }
    }

    /**
     * Payment cancellation
     */
    #[Route('/cancel', name: 'app_payment_cancel', methods: ['GET'])]
    public function cancel(): Response
    {
        $this->addFlash('warning', 'Payment was cancelled. Please try again.');
        return $this->redirectToRoute('app_frontend_commande_index');
    }

    /**
     * Webhook endpoint for Stripe events
     */
    #[Route('/webhook', name: 'app_payment_webhook', methods: ['POST'])]
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->headers->get('stripe-signature');

        try {
            $event = $this->stripeService->verifyWebhook($payload, $signature);

            switch ($event['type']) {
                case 'checkout.session.completed':
                    $this->handleCheckoutCompleted($event);
                    break;
                case 'payment_intent.succeeded':
                    $this->handlePaymentIntentSucceeded($event);
                    break;
                case 'payment_intent.payment_failed':
                    $this->handlePaymentIntentFailed($event);
                    break;
            }

            return new Response('Webhook received', Response::HTTP_OK);
        } catch (\Exception $e) {
            $this->logger->error('Webhook error: ' . $e->getMessage());
            return new Response('Webhook error', Response::HTTP_BAD_REQUEST);
        }
    }

    /**
     * Get invoice for an order
     */
    #[Route('/invoice/{id}', name: 'app_payment_invoice', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function invoice(?Commande $commande): Response
    {
        if (!$commande) {
            throw $this->createNotFoundException('Order not found');
        }

        // Check access: user can only view their own invoices
        if ($commande->getUtilisateur() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have access to this invoice');
        }

        // Get payment record
        $payment = $this->entityManager->getRepository(Payment::class)->findOneBy(['commande' => $commande]);

        return $this->invoiceService->generateInvoicePdf(
            $commande,
            $payment?->getStripePaymentIntentId()
        );
    }

    /**
     * Get payment history for user
     */
    #[Route('/history', name: 'app_payment_history', methods: ['GET'])]
    public function history(): Response
    {
        $user = $this->getUser();
        if (!$user) {
            throw $this->createAccessDeniedException('Login required');
        }

        $payments = $this->entityManager->getRepository(Payment::class)->findBy(
            ['commande' => $this->commandeRepository->findBy(['utilisateur' => $user])],
            ['datePaiement' => 'DESC']
        );

        return $this->render('payment/history.html.twig', [
            'payments' => $payments,
        ]);
    }

    /**
     * API: Get order payment status
     */
    #[Route('/api/status/{id}', name: 'api_payment_status', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function apiPaymentStatus(?Commande $commande): JsonResponse
    {
        if (!$commande) {
            return new JsonResponse(['error' => 'Order not found'], Response::HTTP_NOT_FOUND);
        }

        $payment = $this->entityManager->getRepository(Payment::class)->findOneBy(['commande' => $commande]);

        return new JsonResponse([
            'orderId' => $commande->getId(),
            'status' => $commande->getStatut(),
            'isPaid' => $commande->getStatut() === 'payee',
            'payment' => $payment ? [
                'id' => $payment->getId(),
                'montant' => $payment->getMontant(),
                'statut' => $payment->getStatut(),
                'methode' => $payment->getMethodePaiement(),
                'datePaiement' => $payment->getDatePaiement()?->format('Y-m-d H:i:s'),
            ] : null,
        ]);
    }

    private function handleCheckoutCompleted(array $event): void
    {
        $data = $this->stripeService->handleCheckoutCompleted($event);
        $this->logger->info('Checkout completed: ' . json_encode($data));
    }

    private function handlePaymentIntentSucceeded(array $event): void
    {
        $data = $this->stripeService->handlePaymentSucceeded($event);
        $this->logger->info('Payment intent succeeded: ' . json_encode($data));
    }

    private function handlePaymentIntentFailed(array $event): void
    {
        $data = $this->stripeService->handlePaymentFailed($event);
        $this->logger->error('Payment intent failed: ' . json_encode($data));
    }
}
