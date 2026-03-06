<?php

namespace App\Controller;

use App\Entity\Commande;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class StripeController extends AbstractController
{
    #[Route('/commande/create-session/{id}', name: 'stripe_create_session')]
    public function createSession(EntityManagerInterface $em, $id): Response
    {
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        $commande = $em->getRepository(Commande::class)->find($id);

        if (!$commande) {
            $this->addFlash('error', 'Commande non trouvée');
            return $this->redirectToRoute('app_panier_index');
        }

        $line_items = [];
        foreach ($commande->getLignes() as $ligne) {
            $line_items[] = [
                'price_data' => [
                    'currency' => 'tnd',
                    'unit_amount' => (int)($ligne->getPrix() * 1000),
                    'product_data' => [
                        'name' => $ligne->getNom(),
                    ],
                ],
                'quantity' => $ligne->getQuantite(),
            ];
        }

        $checkout_session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => $line_items,
            'mode' => 'payment',
            'success_url' => $this->generateUrl('stripe_success', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('stripe_cancel', ['id' => $commande->getId()], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);

        return $this->redirect($checkout_session->url, 303);
    }

    #[Route('/commande/success/{id}', name: 'stripe_success')]
    public function success(EntityManagerInterface $em, $id): Response
    {
        $commande = $em->getRepository(Commande::class)->find($id);

        if ($commande) {
            $commande->setStatut('payee');
            $em->flush();
        }

        $this->addFlash('success', 'Paiement effectué avec succès!');
        return $this->redirectToRoute('app_frontend_commande_show', ['id' => $id]);
    }

    #[Route('/commande/cancel/{id}', name: 'stripe_cancel')]
    public function cancel(EntityManagerInterface $em, $id): Response
    {
        $commande = $em->getRepository(Commande::class)->find($id);

        if ($commande) {
            $commande->setStatut('annule');
            $em->flush();
        }

        $this->addFlash('error', 'Le paiement a été annulé.');
        return $this->redirectToRoute('app_commande_index');
    }
}
