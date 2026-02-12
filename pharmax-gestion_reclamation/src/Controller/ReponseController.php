<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reponses')]
class ReponseController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'reponse_index', methods: ['GET'])]
    public function index(): Response
    {
        $reponses = $this->em->getRepository(Reponse::class)->findAll();
        return $this->render('reponse/index.html.twig', [
            'reponses' => $reponses,
        ]);
    }

    #[Route('/new/{reclamationId}', name: 'reponse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $reclamationId): Response
    {
        $reclamation = $this->em->getRepository(Reclamation::class)->find($reclamationId);
        
        if (!$reclamation) {
            throw $this->createNotFoundException('Reclamation not found');
        }

        $reponse = new Reponse();
        $reponse->setReclamation($reclamation);

        if ($request->isMethod('POST')) {
            $reponse->setContenu($request->request->get('contenu'));

            $this->em->persist($reponse);
            // when a response is added, mark reclamation as resolved
            $reclamation->setStatut('Resolu');
            $this->em->persist($reclamation);

            $this->em->flush();

            return $this->redirectToRoute('reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('reponse/new.html.twig', [
            'reponse' => $reponse,
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}', name: 'reponse_show', methods: ['GET'])]
    public function show(Reponse $reponse): Response
    {
        return $this->render('reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse): Response
    {
        if ($request->isMethod('POST')) {
            $reponse->setContenu($request->request->get('contenu'));

            $this->em->flush();

            return $this->redirectToRoute('reclamation_show', ['id' => $reponse->getReclamation()->getId()]);
        }

        return $this->render('reponse/edit.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}', name: 'reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse): Response
    {
        $reclamation = $reponse->getReclamation();
        $reclamationId = $reclamation->getId();

        if ($this->isCsrfTokenValid('delete' . $reponse->getId(), $request->request->get('_token'))) {
            $this->em->remove($reponse);
            $this->em->flush();

            // if no more responses for this reclamation, set status back to 'En attente'
            $remaining = $this->em->getRepository(Reponse::class)->count(['reclamation' => $reclamation]);
            if ($remaining === 0) {
                $reclamation->setStatut('En attente');
                $this->em->persist($reclamation);
                $this->em->flush();
            }
        }

        return $this->redirectToRoute('reclamation_show', ['id' => $reclamationId]);
    }
}
