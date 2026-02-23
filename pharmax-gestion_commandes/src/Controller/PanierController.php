<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/panier')]
class PanierController extends AbstractController
{
    #[Route('/', name: 'app_panier_index')]
    public function index(Request $request): Response
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $panier = $session->get('panier', []);
        $total = 0;

        foreach ($panier as $item) {
            $total += $item['prix'] * $item['quantite'];
        }

        return $this->render('frontend/panier/index.html.twig', [
            'panier' => $panier,
            'total' => $total,
        ]);
    }

    #[Route('/ajouter/{id}', name: 'app_panier_ajouter')]
    public function ajouter(int $id, Request $request, ProduitRepository $produitRepository): Response
    {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            $this->addFlash('error', 'Produit non trouvé!');
            return $this->redirectToRoute('app_produit_index');
        }

        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $panier = $session->get('panier', []);

        // Vérifier si le produit est déjà dans le panier
        if (isset($panier[$id])) {
            $panier[$id]['quantite']++;
        } else {
            $panier[$id] = [
                'id' => $id,
                'nom' => $produit->getNom(),
                'prix' => $produit->getPrix(),
                'image' => $produit->getImage(),
                'quantite' => 1,
            ];
        }

        $session->set('panier', $panier);

        $count = count($session->get('panier', []));
        $this->addFlash('success', sprintf('Produit ajouté au panier! (%d article(s))', $count));

        // Retourner à la page précédente (referer) pour permettre d'ajouter plusieurs produits
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_produit_index');
    }

    #[Route('/retirer/{id}', name: 'app_panier_retirer')]
    public function retirer(int $id, Request $request): Response
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $panier = $session->get('panier', []);

        if (isset($panier[$id])) {
            unset($panier[$id]);
            $session->set('panier', $panier);
            $this->addFlash('success', 'Produit retiré du panier!');
        }

        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/vider', name: 'app_panier_vider')]
    public function vider(Request $request): Response
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $session->set('panier', []);
        $this->addFlash('success', 'Panier vidé!');

        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/commander', name: 'app_panier_commander', methods: ['POST'])]
    public function commander(Request $request, EntityManagerInterface $em, ProduitRepository $produitRepository): Response
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $panier = $session->get('panier', []);

        if (empty($panier)) {
            $this->addFlash('error', 'Le panier est vide!');
            return $this->redirectToRoute('app_panier_index');
        }

        $total = 0;

        // Créer la commande
        $commande = new Commande();
        // Si vous avez un système d'authentification
        // $commande->setUtilisateur($this->getUser());

        foreach ($panier as $item) {
            $sous = $item['prix'] * $item['quantite'];
            $total += $sous;

            $ligne = new LigneCommande();
            $ligne->setNom($item['nom'])
                ->setPrix((float)$item['prix'])
                ->setQuantite((int)$item['quantite'])
                ->setSousTotal((float)$sous);

            $commande->addLigne($ligne);
        }

        // keep JSON snapshot for legacy / reporting
        $commande->setProduits($panier);
        $commande->setTotales($total);

        $em->persist($commande);
        $em->flush();

        // Envoi d'un e-mail de confirmation si le service mailer est disponible
        try {
            if ($this->container && $this->container->has('mailer')) {
                $mailer = $this->container->get('mailer');
                
                // Récupère l'email de l'utilisateur
                $userEmail = null;
                if (method_exists($commande, 'getUtilisateur') && $commande->getUtilisateur() && method_exists($commande->getUtilisateur(), 'getEmail')) {
                    $userEmail = $commande->getUtilisateur()->getEmail();
                }

                // prefer TemplatedEmail if available
                if (class_exists(\Symfony\Bridge\Twig\Mime\TemplatedEmail::class)) {
                    $email = (new \Symfony\Bridge\Twig\Mime\TemplatedEmail())
                        ->from(new \Symfony\Component\Mime\Address('noreply@pharmax.example', 'Pharmax'))
                        ->subject(sprintf('Confirmation de votre commande #%d', $commande->getId()))
                        ->htmlTemplate('emails/commande_confirmation.html.twig')
                        ->context(['commande' => $commande]);

                    // Envoyer principalement à l'utilisateur, avec copie admin
                    if ($userEmail) {
                        $email->to(new \Symfony\Component\Mime\Address($userEmail, $commande->getUtilisateur()->getEmail() ?? 'Client'));
                        $email->bcc(new \Symfony\Component\Mime\Address('orders@pharmax.example', 'Pharmax Orders'));
                    } else {
                        // Fallback: si pas d'utilisateur, envoyer à admin
                        $email->to(new \Symfony\Component\Mime\Address('orders@pharmax.example', 'Pharmax Orders'));
                    }

                    $mailer->send($email);
                } elseif (class_exists(\Symfony\Component\Mime\Email::class)) {
                    // fallback: render HTML and send simple Email
                    $html = $this->renderView('emails/commande_confirmation.html.twig', ['commande' => $commande]);
                    $email = (new \Symfony\Component\Mime\Email())
                        ->from('noreply@pharmax.example')
                        ->subject(sprintf('Confirmation de votre commande #%d', $commande->getId()))
                        ->html($html);

                    // Envoyer principalement à l'utilisateur, avec copie admin
                    if ($userEmail) {
                        $email->to($userEmail);
                        $email->bcc('orders@pharmax.example');
                    } else {
                        // Fallback: si pas d'utilisateur, envoyer à admin
                        $email->to('orders@pharmax.example');
                    }

                    $mailer->send($email);
                }
            }
        } catch (\Throwable $e) {
            // don't break checkout on email failure; optionally log
        }

        // Vider le panier
        $session->set('panier', []);

        return $this->redirectToRoute('app_frontend_commande_show', ['id' => $commande->getId()]);
    }
}
