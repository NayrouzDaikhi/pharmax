<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategorieType;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/categorie')]
final class CategorieController extends AbstractController
{
    #[Route(name: 'app_categorie_index', methods: ['GET'])]
    public function index(Request $request, CategorieRepository $categorieRepository): Response
    {
        $search = $request->query->get('search', '');
        $sortBy = $request->query->get('sortBy', 'createdAt');
        $sortOrder = $request->query->get('sortOrder', 'DESC');

        $categories = $categorieRepository->findByFilters($search, $sortBy, $sortOrder);

        return $this->render('categorie/sneat_index.html.twig', [
            'categories' => $categories,
            'search' => $search,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/add', name: 'app_categorie_add_default')]
    public function addCategories(EntityManagerInterface $em): Response
    {
        $categories = [
            ['nom' => 'Visage', 'description' => 'Produits pour le visage'],
            ['nom' => 'Corps', 'description' => 'Produits pour le corps'],
            ['nom' => 'Cheveux', 'description' => 'Produits pour les cheveux'],
            ['nom' => 'Maman et Bébé', 'description' => 'Produits pour maman et bébé'],
            ['nom' => 'Complément Alimentaire', 'description' => 'Compléments alimentaires'],
            ['nom' => 'Matériel Médical', 'description' => 'Matériel médical et équipements'],
        ];

        foreach ($categories as $data) {
            // Vérifier si la catégorie existe déjà
            $existing = $em->getRepository(Categorie::class)->findOneBy(['nom' => $data['nom']]);
            if (!$existing) {
                $cat = new Categorie();
                $cat->setNom($data['nom']);
                $cat->setDescription($data['description']);
                $cat->setCreatedAt(new \DateTime());
                $em->persist($cat);
            }
        }

        $em->flush();

        return $this->redirectToRoute('app_categorie_index');
    }

    #[Route('/new', name: 'app_categorie_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $categorie = new Categorie();
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // S'assurer que createdAt est défini
            if (!$categorie->getCreatedAt()) {
                $categorie->setCreatedAt(new \DateTime());
            }

            $entityManager->persist($categorie);
            $entityManager->flush();

            $this->addFlash('success', 'Catégorie créée avec succès!');
            return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie/sneat_new.html.twig', [
            'categorie' => $categorie,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_show', methods: ['GET'])]
    public function show(Categorie $categorie): Response
    {
        return $this->render('categorie/sneat_show.html.twig', [
            'categorie' => $categorie,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_categorie_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategorieType::class, $categorie);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Catégorie modifiée avec succès!');
            return $this->redirectToRoute('app_categorie_show', ['id' => $categorie->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('categorie/sneat_edit.html.twig', [
            'form' => $form,
            'categorie' => $categorie
        ]);
    }

    #[Route('/{id}', name: 'app_categorie_delete', methods: ['POST'])]
    public function delete(Request $request, Categorie $categorie, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$categorie->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($categorie);
            $entityManager->flush();
            $this->addFlash('success', 'Catégorie supprimée avec succès!');
        }

        return $this->redirectToRoute('app_categorie_index', [], Response::HTTP_SEE_OTHER);
    }
}
