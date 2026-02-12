<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\LigneCommande;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => false, 'message' => 'Produit non trouvé!'], 404);
            }
            $this->addFlash('error', 'Produit non trouvé!');
            return $this->redirectToRoute('app_panier_index');
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
        $message = sprintf('Produit ajouté au panier! (%d article(s))', $count);

        // Répondre en JSON si c'est une requête AJAX
        if ($request->isXmlHttpRequest()) {
            return new JsonResponse([
                'success' => true,
                'message' => $message,
                'count' => $count,
            ]);
        }

        $this->addFlash('success', $message);

        // Retourner à la page précédente (referer) pour permettre d'ajouter plusieurs produits
        $referer = $request->headers->get('referer');
        if ($referer) {
            return $this->redirect($referer);
        }

        return $this->redirectToRoute('app_front_produits');
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

    #[Route('/augmenter/{id}', name: 'app_panier_augmenter')]
    public function augmenter(int $id, Request $request): Response
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $panier = $session->get('panier', []);

        if (isset($panier[$id])) {
            $panier[$id]['quantite']++;
            $session->set('panier', $panier);
            $this->addFlash('success', 'Quantité augmentée!');
        }

        return $this->redirectToRoute('app_panier_index');
    }

    #[Route('/diminuer/{id}', name: 'app_panier_diminuer')]
    public function diminuer(int $id, Request $request): Response
    {
        $session = $request->getSession();
        if (!$session->isStarted()) {
            $session->start();
        }

        $panier = $session->get('panier', []);

        if (isset($panier[$id])) {
            if ($panier[$id]['quantite'] > 1) {
                $panier[$id]['quantite']--;
                $session->set('panier', $panier);
                $this->addFlash('success', 'Quantité diminuée!');
            } else {
                // Si quantité = 1, retirer le produit
                unset($panier[$id]);
                $session->set('panier', $panier);
                $this->addFlash('success', 'Produit retiré du panier!');
            }
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

        // Vider le panier
        $session->set('panier', []);

        return $this->redirectToRoute('app_frontend_commande_show', ['id' => $commande->getId()]);
    }
}
