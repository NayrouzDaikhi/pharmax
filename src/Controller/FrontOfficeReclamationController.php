<?php

namespace App\Controller;

use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/reclamations')]
class FrontOfficeReclamationController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private ValidatorInterface $validator)
    {
    }

    #[Route('', name: 'frontend_reclamation_index', methods: ['GET'])]
    public function index(): Response
    {
        $reclamations = $this->em->getRepository(Reclamation::class)->findAll();
        return $this->render('frontend/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/new', name: 'frontend_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $errors = [];
        $titre = '';
        $description = '';

        if ($request->isMethod('POST')) {
            $titre = trim($request->request->get('titre', ''));
            $description = trim($request->request->get('description', ''));

            // Sanitize les données (supprimer les balises HTML/scripts)
            $titre = strip_tags($titre);
            $description = strip_tags($description);

            // Création de l'entité
            $reclamation = new Reclamation();
            $reclamation->setTitre($titre);
            $reclamation->setDescription($description);

            // Valider l'entité avec Symfony Validator
            $violations = $this->validator->validate($reclamation);

            if (count($violations) > 0) {
                // Collecter les erreurs de validation
                foreach ($violations as $violation) {
                    $propertyPath = $violation->getPropertyPath();
                    $errors[$propertyPath] = $violation->getMessage();
                }
            } else {
                // Si pas d'erreurs, créer la réclamation
                $reclamation->setStatut('En attente');
                $this->em->persist($reclamation);
                $this->em->flush();

                return $this->redirectToRoute('frontend_reclamation_show', ['id' => $reclamation->getId()]);
            }
        }

        return $this->render('frontend/reclamation/new.html.twig', [
            'titre' => $titre,
            'description' => $description,
            'errors' => $errors,
        ]);
    }

    #[Route('/{id}', name: 'frontend_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('frontend/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'frontend_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation): Response
    {
        $errors = [];
        $titre = $reclamation->getTitre() ?? '';
        $description = $reclamation->getDescription() ?? '';

        if ($request->isMethod('POST')) {
            $titre = trim($request->request->get('titre', ''));
            $description = trim($request->request->get('description', ''));

            // Sanitize les données (supprimer les balises HTML/scripts)
            $titre = strip_tags($titre);
            $description = strip_tags($description);

            // Definir les valeurs sur l'entité
            $reclamation->setTitre($titre);
            $reclamation->setDescription($description);

            // Valider l'entité avec Symfony Validator
            $violations = $this->validator->validate($reclamation);

            if (count($violations) > 0) {
                // Collecter les erreurs de validation
                foreach ($violations as $violation) {
                    $propertyPath = $violation->getPropertyPath();
                    $errors[$propertyPath] = $violation->getMessage();
                }
                // Revenir aux anciennes valeurs pour l'affichage du formulaire
                $titre = $reclamation->getTitre();
                $description = $reclamation->getDescription();
            } else {
                // Si pas d'erreurs, mettre à jour la réclamation
                $this->em->flush();

                return $this->redirectToRoute('frontend_reclamation_show', ['id' => $reclamation->getId()]);
            }
        }

        return $this->render('frontend/reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'titre' => $titre,
            'description' => $description,
            'errors' => $errors,
        ]);
    }

    #[Route('/{id}', name: 'frontend_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $this->em->remove($reclamation);
            $this->em->flush();
        }

        return $this->redirectToRoute('frontend_reclamation_index');
    }
}
