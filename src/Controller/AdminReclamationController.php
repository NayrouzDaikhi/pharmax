<?php

namespace App\Controller;

use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use App\Entity\Reponse;
use App\Form\ReponseType;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/admin/reclamation')]
class AdminReclamationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private PaginatorInterface $paginator
    ) {
    }

    #[Route('', name: 'admin_reclamation_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $search = $request->query->get('search', '');
        $statut = $request->query->get('statut', '');
        $date = $request->query->get('date', '');
        $sortBy = $request->query->get('sortBy', 'r.dateCreation');
        $sortOrder = $request->query->get('sortOrder', 'DESC');
        $page = $request->query->get('page', 1);

        // Valider l'ordre de tri
        $sortOrder = strtoupper($sortOrder) === 'ASC' ? 'ASC' : 'DESC';
        
        // Valider et normaliser la colonne de tri : accepter "alias.field" ou clé courte
        $mapping = [
            'id' => 'r.id',
            'titre' => 'r.titre',
            'utilisateur' => 'u.firstName',
            'statut' => 'r.statut',
            'dateCreation' => 'r.dateCreation',
        ];
        if (isset($mapping[$sortBy])) {
            $sortField = $mapping[$sortBy];
        } elseif (str_contains($sortBy, '.')) {
            $sortField = $sortBy; // already qualified
        } else {
            $sortField = $mapping['dateCreation'];
            $sortBy = $sortField;
        }

          $qb = $this->em->getRepository(Reclamation::class)->createQueryBuilder('r');
          // sélectionner explicitement les alias utilisés pour éviter les problèmes
          $qb->select('r')
              ->leftJoin('r.user', 'u')
              ->addSelect('u');

        // Recherche combinée Titre + Utilisateur
        if ($search) {
            $qb->andWhere('(r.titre LIKE :search OR u.firstName LIKE :search OR u.lastName LIKE :search OR u.email LIKE :search)')
               ->setParameter('search', '%' . $search . '%');
        }

        if ($statut) {
            $qb->andWhere('r.statut = :statut')
               ->setParameter('statut', $statut);
        }

        if ($date) {
            $dateObj = \DateTime::createFromFormat('Y-m-d', $date);
            if ($dateObj) {
                $dateEnd = (clone $dateObj)->modify('+1 day');
                $qb->andWhere('r.dateCreation >= :dateStart')
                   ->andWhere('r.dateCreation < :dateEnd')
                   ->setParameter('dateStart', $dateObj)
                   ->setParameter('dateEnd', $dateEnd);
            }
        }

        // Appliquer le tri en utilisant le champ normalisé
        $qb->orderBy($sortField, $sortOrder);

        // Paginer les résultats
        // Paginer en affichant 10 éléments par page (per le besoin)
        $reclamations = $this->paginator->paginate(
            $qb, // passer le QueryBuilder pour que KNP gère correctement le count
            $page,
            10 // 10 éléments par page
        );

        $filters = [
            'search' => $search,
            'statut' => $statut,
            'date' => $date,
        ];

        // statistiques générales
        $repo = $this->em->getRepository(Reclamation::class);
        $totalCount = $repo->count([]);
        $countEnAttente = $repo->count(['statut' => 'En attente']);
        $countEnCours = $repo->count(['statut' => 'En cours']);
        $countResolu = $repo->count(['statut' => 'Resolu']);

        // Déterminer l'ordre opposé pour les liens de tri
        $nextSortOrder = $sortOrder === 'ASC' ? 'DESC' : 'ASC';

        return $this->render('admin/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'filters' => $filters,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
            'nextSortOrder' => $nextSortOrder,
            'stats' => [
                'total' => $totalCount,
                'en_attente' => $countEnAttente,
                'en_cours' => $countEnCours,
                'resolu' => $countResolu,
            ],
        ]);
    }

    #[Route('/{id}', name: 'admin_reclamation_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Reclamation $reclamation): Response
    {
        $reponse = new Reponse();
        $reponseForm = $this->createForm(ReponseType::class, $reponse);
        $reponseForm->handleRequest($request);

        if ($reponseForm->isSubmitted() && $reponseForm->isValid()) {
            $reponse->setReclamation($reclamation);
            $reponse->setUser($this->getUser());
            $reponse->setDateReponse(new \DateTime());

            $this->em->persist($reponse);
            $this->em->flush();

            $this->addFlash('success', 'Réponse envoyée avec succès.');

            return $this->redirectToRoute('admin_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('admin/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
            'reponseForm' => $reponseForm->createView(),
        ]);
    }

    #[Route('/{id}/generate-ai-response', name: 'admin_reclamation_generate_ai_response', methods: ['POST'])]
    public function generateAiResponse(Reclamation $reclamation): JsonResponse
    {
        // Simulation d'un appel à une IA
        $prompt = $reclamation->getDescription();
        $shortPrompt = substr($prompt, 0, 50) . (strlen($prompt) > 50 ? '...' : '');

        $responseTemplates = [
            "Bonjour, \n\nSuite à votre réclamation concernant : \"{$shortPrompt}\".\n\nNous avons le plaisir de vous informer que nous avons traité votre demande et que la situation a été résolue. \n\nN'hésitez pas à nous recontacter si le problème persiste ou si vous avez d'autres questions.\n\nCordialement,\nL'équipe Pharmax",
            "Bonjour, \n\nNous vous confirmons que votre réclamation (sujet: \"{$shortPrompt}\") a bien été résolue par nos services. Nous restons à votre disposition pour toute autre demande.\n\nCordialement,\nL'équipe Pharmax",
            "Bonjour, \n\nNous avons pris les mesures nécessaires concernant votre réclamation (\"{$shortPrompt}\") et nous considérons le problème comme résolu. Merci de votre patience. Si besoin, notre support reste disponible.\n\nCordialement,\nL'équipe Pharmax",
            "Bonjour, \n\nVotre réclamation (\"{$shortPrompt}\") a retenu toute notre attention et nous sommes heureux de vous annoncer qu'une solution a été apportée. Votre satisfaction est notre priorité.\n\nCordialement,\nL'équipe Pharmax",
            "Bonjour, \n\nCeci est une notification pour vous informer que le problème que vous avez signalé (\"{$shortPrompt}\") a été résolu. Nous vous remercions de nous avoir aidés à améliorer nos services.\n\nCordialement,\nL'équipe Pharmax",
            "Bonjour, \n\nBonne nouvelle ! La réclamation que vous aviez soumise au sujet de \"{$shortPrompt}\" est maintenant clôturée. Tout devrait être rentré dans l'ordre. Merci de votre confiance.\n\nCordialement,\nL'équipe Pharmax",
        ];

        $suggestedResponse = $responseTemplates[array_rand($responseTemplates)];

        return new JsonResponse(['response' => $suggestedResponse]);
    }

    #[Route('/{id}/edit', name: 'admin_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation): Response
    {
        if ($request->isMethod('POST')) {
            $reclamation->setStatut($request->request->get('statut'));

            $this->em->flush();

            return $this->redirectToRoute('admin_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('admin/reclamation/edit.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}', name: 'admin_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $this->em->remove($reclamation);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_reclamation_index');
    }
}
