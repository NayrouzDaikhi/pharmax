<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Form\CommentaireType;
use App\Repository\CommentaireRepository;
use App\Repository\CommentaireArchiveRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/commentaire')]
final class CommentaireController extends AbstractController
{
    #[Route(name: 'app_commentaire_index', methods: ['GET'])]
    public function index(CommentaireRepository $commentaireRepository, CommentaireArchiveRepository $archiveRepository): Response
    {
        return $this->render('commentaire/index.html.twig', [
            'commentaires' => $commentaireRepository->findAll(),
            'archived_commentaires' => $archiveRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_commentaire_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commentaire = new Commentaire();
        // Set publication date to current date/time
        $commentaire->setDatePublication(new \DateTime());
        
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($commentaire);
            $entityManager->flush();

            // If article ID is provided, redirect to blog show page instead of article index
            $articleId = $commentaire->getArticle()->getId();
            if ($articleId) {
                return $this->redirectToRoute('app_blog_show', ['id' => $articleId], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire/new.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_show', methods: ['GET'])]
    public function show(Commentaire $commentaire): Response
    {
        return $this->render('commentaire/show.html.twig', [
            'commentaire' => $commentaire,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commentaire_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $articleId = $commentaire->getArticle()->getId();
        
        $form = $this->createForm(CommentaireType::class, $commentaire);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Determine redirect based on referrer or return to blog
            if ($articleId) {
                return $this->redirectToRoute('app_blog_show', ['id' => $articleId], Response::HTTP_SEE_OTHER);
            }

            return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commentaire/edit.html.twig', [
            'commentaire' => $commentaire,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_commentaire_delete', methods: ['POST'])]
    public function delete(Request $request, Commentaire $commentaire, EntityManagerInterface $entityManager): Response
    {
        $articleId = $commentaire->getArticle()->getId();
        
        if ($this->isCsrfTokenValid('delete'.$commentaire->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($commentaire);
            $entityManager->flush();
        }

        // Determine redirect based on article or return to index
        if ($articleId) {
            return $this->redirectToRoute('app_blog_show', ['id' => $articleId], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_article_index', [], Response::HTTP_SEE_OTHER);
    }
}
