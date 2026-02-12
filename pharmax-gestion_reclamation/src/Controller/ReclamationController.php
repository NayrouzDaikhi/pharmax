<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Service\TranslateService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/reclamations')]
class ReclamationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private TranslateService $translateService
    )
    {
    }

    #[Route('', name: 'reclamation_index', methods: ['GET'])]
    public function index(): Response
    {
        $reclamations = $this->em->getRepository(Reclamation::class)->findAll();
        return $this->render('reclamation/index.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/new', name: 'reclamation_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $reclamation = new Reclamation();
        $errors = [];

        if ($request->isMethod('POST')) {
            $titre = trim($request->request->get('titre', ''));
            $description = trim($request->request->get('description', ''));

            // Sanitize les données (supprimer les balises HTML/scripts)
            $titre = strip_tags($titre);
            $description = strip_tags($description);

            // Validation du titre
            if (empty($titre)) {
                $errors['titre'] = 'Le titre est obligatoire.';
            } elseif (strlen($titre) < 3) {
                $errors['titre'] = 'Le titre doit contenir au moins 3 caractères.';
            } elseif (strlen($titre) > 255) {
                $errors['titre'] = 'Le titre ne peut pas dépasser 255 caractères.';
            } elseif (!preg_match('/^[a-zA-Z0-9\s\-\.\,\:\'\"\&àâäæçéèêëïîôöœùûüœÀÂÄÆÇÉÈÊËÏÎÔÖŒÙÛÜŒ]+$/', $titre)) {
                $errors['titre'] = 'Le titre contient des caractères non autorisés.';
            }

            // Validation de la description
            if (empty($description)) {
                $errors['description'] = 'La description est obligatoire.';
            } elseif (strlen($description) < 10) {
                $errors['description'] = 'La description doit contenir au moins 10 caractères.';
            } elseif (strlen($description) > 5000) {
                $errors['description'] = 'La description ne peut pas dépasser 5000 caractères.';
            }

            // Si pas d'erreurs, créer la réclamation
            if (empty($errors)) {
                $reclamation->setTitre($titre);
                $reclamation->setDescription($description);

                // statut is managed by the system (defaults to 'En attente') and not set from user input
                $this->em->persist($reclamation);
                $this->em->flush();

                return $this->redirectToRoute('reclamation_show', ['id' => $reclamation->getId()]);
            } else {
                // Remplir les champs avec les données soumises pour l'affichage
                $reclamation->setTitre($titre);
                $reclamation->setDescription($description);
            }
        }

        return $this->render('reclamation/new.html.twig', [
            'reclamation' => $reclamation,
            'errors' => $errors,
        ]);
    }

    #[Route('/{id}', name: 'reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation): Response
    {
        $errors = [];

        if ($request->isMethod('POST')) {
            $titre = trim($request->request->get('titre', ''));
            $description = trim($request->request->get('description', ''));

            // Sanitize les données (supprimer les balises HTML/scripts)
            $titre = strip_tags($titre);
            $description = strip_tags($description);

            // Validation du titre
            if (empty($titre)) {
                $errors['titre'] = 'Le titre est obligatoire.';
            } elseif (strlen($titre) < 3) {
                $errors['titre'] = 'Le titre doit contenir au moins 3 caractères.';
            } elseif (strlen($titre) > 255) {
                $errors['titre'] = 'Le titre ne peut pas dépasser 255 caractères.';
            } elseif (!preg_match('/^[a-zA-Z0-9\s\-\.\,\:\'\"\&àâäæçéèêëïîôöœùûüœÀÂÄÆÇÉÈÊËÏÎÔÖŒÙÛÜŒ]+$/', $titre)) {
                $errors['titre'] = 'Le titre contient des caractères non autorisés.';
            }

            // Validation de la description
            if (empty($description)) {
                $errors['description'] = 'La description est obligatoire.';
            } elseif (strlen($description) < 10) {
                $errors['description'] = 'La description doit contenir au moins 10 caractères.';
            } elseif (strlen($description) > 5000) {
                $errors['description'] = 'La description ne peut pas dépasser 5000 caractères.';
            }

            // Si pas d'erreurs, mettre à jour la réclamation
            if (empty($errors)) {
                $reclamation->setTitre($titre);
                $reclamation->setDescription($description);

                // prevent users from changing statut directly
                $this->em->flush();

                return $this->redirectToRoute('reclamation_show', ['id' => $reclamation->getId()]);
            }
        }

        return $this->render('reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
            'errors' => $errors,
        ]);
    }

    #[Route('/{id}', name: 'reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $this->em->remove($reclamation);
            $this->em->flush();
        }

        return $this->redirectToRoute('reclamation_index');
    }

    /**
     * Traduit une réclamation vers une langue donnée
     * 
     * @Route("/{id}/translate/{targetLang}", name="reclamation_translate", methods=["GET"])
     */
    #[Route('/{id}/translate/{targetLang}', name: 'reclamation_translate', methods: ['GET'])]
    public function translate(Reclamation $reclamation, string $targetLang = 'en'): JsonResponse
    {
        $titreFormatted = $this->translateService->translateText($reclamation->getTitre(), $targetLang);
        $descriptionFormatted = $this->translateService->translateText($reclamation->getDescription(), $targetLang);

        return $this->json([
            'id' => $reclamation->getId(),
            'titre_original' => $reclamation->getTitre(),
            'titre_traduit' => $titreFormatted,
            'description_original' => $reclamation->getDescription(),
            'description_traduite' => $descriptionFormatted,
            'langue_cible' => $targetLang,
            'statut' => $reclamation->getStatut()
        ]);
    }
}
