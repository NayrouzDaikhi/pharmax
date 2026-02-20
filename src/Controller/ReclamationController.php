<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Service\GoogleTranslationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/reclamations')]
class ReclamationController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em, private ValidatorInterface $validator)
    {
    }

    #[Route('', name: 'frontend_reclamation_index', methods: ['GET'])]
    public function index(): Response
    {
        // Require user to be authenticated
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        // Get only reclamations belonging to the current user
        $reclamations = $this->em->getRepository(Reclamation::class)->findByUser($user);
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/new', name: 'frontend_reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        // Require user to be authenticated
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        $errors = [];
        $titre = '';
        $description = '';

        if ($request->isMethod('POST')) {
            $titre = trim($request->request->get('titre', ''));
            $description = trim($request->request->get('description', ''));

            // Validation PHP personnalisée
            $phpErrors = $this->validateReclamation($titre, $description);
            if (!empty($phpErrors)) {
                $errors = $phpErrors;
            } else {
                // Sanitize les données (supprimer les balises HTML/scripts)
                $titre = strip_tags($titre);
                $description = strip_tags($description);

                // Création de l'entité
                $reclamation = new Reclamation();
                $reclamation->setTitre($titre);
                $reclamation->setDescription($description);
                $reclamation->setUser($user); // Track the logged-in user

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
        }

        return $this->render('reclamation/new.html.twig', [
            'titre' => $titre,
            'description' => $description,
            'errors' => $errors,
        ]);
    }

    #[Route('/{id}', name: 'frontend_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        // Require user to be authenticated
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        // Check if the user owns this reclamation or is an admin
        if ($reclamation->getUser() !== $user && !$this->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException('You can only view your own reclamations.');
        }
        
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'frontend_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation): Response
    {
        // Require user to be authenticated
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        // Check if the user owns this reclamation
        if ($reclamation->getUser() !== $user) {
            throw $this->createAccessDeniedException('You can only edit your own reclamations.');
        }
        
        $errors = [];
        $titre = $reclamation->getTitre() ?? '';
        $description = $reclamation->getDescription() ?? '';

        if ($request->isMethod('POST')) {
            $titre = trim($request->request->get('titre', ''));
            $description = trim($request->request->get('description', ''));

            // Validation PHP personnalisée
            $phpErrors = $this->validateReclamation($titre, $description);
            if (!empty($phpErrors)) {
                $errors = $phpErrors;
            } else {
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
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'titre' => $titre,
            'description' => $description,
            'errors' => $errors,
        ]);
    }

    #[Route('/{id}', name: 'frontend_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation): Response
    {
        // Require user to be authenticated
        $this->denyAccessUnlessGranted('ROLE_USER');
        
        $user = $this->getUser();
        
        // Check if the user owns this reclamation
        if ($reclamation->getUser() !== $user) {
            throw $this->createAccessDeniedException('You can only delete your own reclamations.');
        }
        
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $this->em->remove($reclamation);
            $this->em->flush();
        }

        return $this->redirectToRoute('frontend_reclamation_index');
    }

    #[Route('/api/translate', name: 'app_translate', methods: ['POST'])]
    public function translate(
        Request $request,
        GoogleTranslationService $translationService
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['text']) || !isset($data['target_lang'])) {
            return new JsonResponse(['error' => 'Missing text or target_lang'], Response::HTTP_BAD_REQUEST);
        }

        $text = $data['text'];
        $targetLang = $data['target_lang'];

        try {
            $translated = $translationService->translate($text, $targetLang);
            return new JsonResponse(['translated' => $translated]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Translation failed: ' . $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Valide les données de la réclamation en PHP
     */
    private function validateReclamation(string $titre, string $description): array
    {
        $errors = [];

        // Validation du titre
        if (empty($titre)) {
            $errors['titre'] = 'Le titre est obligatoire.';
        } elseif (strlen($titre) < 5) {
            $errors['titre'] = 'Le titre doit contenir au moins 5 caractères.';
        } elseif (strlen($titre) > 255) {
            $errors['titre'] = 'Le titre ne doit pas dépasser 255 caractères.';
        }

        // Validation de la description
        if (empty($description)) {
            $errors['description'] = 'La description est obligatoire.';
        } elseif (strlen($description) < 20) {
            $errors['description'] = 'La description doit contenir au moins 20 caractères.';
        } elseif (strlen($description) > 2000) {
            $errors['description'] = 'La description ne doit pas dépasser 2000 caractères.';
        }

        return $errors;
    }
}
