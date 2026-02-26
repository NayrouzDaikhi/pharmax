<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\Produit1Type;
use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Filesystem\Filesystem;

#[Route('/admin/produit')]
final class ProduitController extends AbstractController
{
    #[Route(name: 'app_produit_index', methods: ['GET'])]
    public function index(
        Request $request,
        ProduitRepository $produitRepository,
        CategorieRepository $categorieRepository,
        PaginatorInterface $paginator
    ): Response {
        $search = $request->query->get('search', '');
        $categorie = $request->query->get('categorie', '');
        $sortBy = $request->query->get('sortBy', 'p.createdAt');
        $sortOrder = $request->query->get('sortOrder', 'DESC');

        $qb = $produitRepository->createFilteredQueryBuilder($search, $categorie, $sortBy, $sortOrder);

        $page = max(1, (int) $request->query->get('page', 1));
        // 2 produits par page
        $limit = 2;

        $produits = $paginator->paginate(
            $qb,
            $page,
            $limit,
            [
                \Knp\Component\Pager\PaginatorInterface::SORT_FIELD_ALLOW_LIST => ['p.nom', 'p.prix', 'p.createdAt', 'p.dateExpiration', 'p.quantite'],
            ]
        );

        $categories = $categorieRepository->findAll();

        return $this->render('produit/sneat_index.html.twig', [
            'produits' => $produits,
            'categories' => $categories,
            'search' => $search,
            'categorie' => $categorie,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $produit = new Produit();
        $form = $this->createForm(Produit1Type::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation PHP des données
            $validationErrors = $this->validateProduit($produit, $form);
            if (!empty($validationErrors)) {
                foreach ($validationErrors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('produit/sneat_new.html.twig', [
                    'produit' => $produit,
                    'form' => $form,
                ]);
            }

            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                $imageValidationErrors = $this->validateImageFile($imageFile);
                if (!empty($imageValidationErrors)) {
                    foreach ($imageValidationErrors as $error) {
                        $this->addFlash('error', $error);
                    }
                    return $this->render('produit/sneat_new.html.twig', [
                        'produit' => $produit,
                        'form' => $form,
                    ]);
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/images',
                        $newFilename
                    );
                    $produit->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                    return $this->render('produit/sneat_new.html.twig', [
                        'produit' => $produit,
                        'form' => $form,
                    ]);
                }
            }

            $produit->setCreatedAt(new \DateTime());
            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit créé avec succès!');
            return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/sneat_new.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/sneat_show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(Produit1Type::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Validation PHP des données
            $validationErrors = $this->validateProduit($produit, $form);
            if (!empty($validationErrors)) {
                foreach ($validationErrors as $error) {
                    $this->addFlash('error', $error);
                }
                return $this->render('produit/sneat_edit.html.twig', [
                    'produit' => $produit,
                    'form' => $form,
                ]);
            }

            $imageFile = $form->get('image')->getData();
            
            if ($imageFile) {
                $imageValidationErrors = $this->validateImageFile($imageFile);
                if (!empty($imageValidationErrors)) {
                    foreach ($imageValidationErrors as $error) {
                        $this->addFlash('error', $error);
                    }
                    return $this->render('produit/sneat_edit.html.twig', [
                        'produit' => $produit,
                        'form' => $form,
                    ]);
                }

                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('kernel.project_dir') . '/public/uploads/images',
                        $newFilename
                    );
                    $produit->setImage($newFilename);
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload de l\'image');
                    return $this->render('produit/sneat_edit.html.twig', [
                        'produit' => $produit,
                        'form' => $form,
                    ]);
                }
            }

            $entityManager->flush();
            $this->addFlash('success', 'Produit modifié avec succès!');
            return $this->redirectToRoute('app_produit_show', ['id' => $produit->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('produit/sneat_edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($produit);
            $entityManager->flush();
            $this->addFlash('success', 'Produit supprimé avec succès!');
        }

        return $this->redirectToRoute('app_produit_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Valide les données du produit en PHP
     */
    private function validateProduit(Produit $produit, $form): array
    {
        $errors = [];

        // Validation du nom
        $nom = $produit->getNom();
        if (empty($nom) || trim($nom) === '') {
            $errors[] = 'Le nom du produit est obligatoire.';
        } elseif (strlen($nom) < 3 || strlen($nom) > 255) {
            $errors[] = 'Le nom du produit doit contenir entre 3 et 255 caractères.';
        }

        // Validation de la description
        $description = $produit->getDescription();
        if (empty($description) || trim($description) === '') {
            $errors[] = 'La description du produit est obligatoire.';
        } elseif (strlen($description) < 10) {
            $errors[] = 'La description doit contenir au moins 10 caractères.';
        }

        // Validation du prix
        $prix = $produit->getPrix();
        if ($prix === null || $prix < 0) {
            $errors[] = 'Le prix doit être supérieur à 0.';
        } elseif (!is_numeric($prix)) {
            $errors[] = 'Le prix doit être un nombre.';
        }

        // Validation de la quantité
        $quantite = $produit->getQuantite();
        if ($quantite === null || !is_numeric($quantite) || $quantite < 0) {
            $errors[] = 'La quantité doit être un nombre positif.';
        }

        return $errors;
    }

    /**
     * Valide le fichier image
     */
    private function validateImageFile($imageFile): array
    {
        $errors = [];
        $maxFileSize = 5 * 1024 * 1024; // 5 MB

        if ($imageFile->getSize() > $maxFileSize) {
            $errors[] = 'La taille du fichier image ne doit pas dépasser 5 MB.';
        }

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower($imageFile->guessExtension());
        if (!in_array($extension, $allowedExtensions)) {
            $errors[] = 'Le format d\'image n\'est pas autorisé. Formats acceptés: JPG, PNG, GIF.';
        }

        return $errors;
    }
}

