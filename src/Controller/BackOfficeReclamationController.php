<?php

namespace App\Controller;

use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reclamations')]
class BackOfficeReclamationController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'admin_reclamation_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $titre = $request->query->get('titre', '');
        $statut = $request->query->get('statut', '');
        $date = $request->query->get('date', '');

        $qb = $this->em->getRepository(Reclamation::class)->createQueryBuilder('r');

        if ($titre) {
            $qb->andWhere('r.titre LIKE :titre')
               ->setParameter('titre', '%' . $titre . '%');
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

        $qb->orderBy('r.dateCreation', 'DESC');
        $reclamations = $qb->getQuery()->getResult();

        $filters = [
            'titre' => $titre,
            'statut' => $statut,
            'date' => $date,
        ];

        return $this->render('backend/reclamation/index.html.twig', [
            'reclamations' => $reclamations,
            'filters' => $filters,
        ]);
    }

    #[Route('/{id}', name: 'admin_reclamation_show', methods: ['GET'])]
    public function show(Reclamation $reclamation): Response
    {
        return $this->render('backend/reclamation/show.html.twig', [
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_reclamation_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reclamation $reclamation): Response
    {
        if ($request->isMethod('POST')) {
            $reclamation->setStatut($request->request->get('statut'));

            $this->em->flush();

            return $this->redirectToRoute('admin_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('backend/reclamation/edit.html.twig', [
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
