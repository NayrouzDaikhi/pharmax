<?php

namespace App\Controller;

use App\Entity\Reponse;
use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/reponses')]
class AdminReponseController extends AbstractController
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'admin_reponse_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $contenu = $request->query->get('contenu');
        $date = $request->query->get('date');

        $qb = $this->em->getRepository(Reponse::class)
                       ->createQueryBuilder('r');

        // 🔍 Filtre par contenu
        if (!empty($contenu)) {
            $qb->andWhere('r.contenu LIKE :contenu')
               ->setParameter('contenu', '%' . $contenu . '%');
        }

        // 📅 Filtre par date
        if (!empty($date)) {
            $dateObj = new \DateTime($date);
            $start = (clone $dateObj)->setTime(0, 0, 0);
            $end   = (clone $dateObj)->setTime(23, 59, 59);

            $qb->andWhere('r.dateReponse BETWEEN :start AND :end')
               ->setParameter('start', $start)
               ->setParameter('end', $end);
        }

        $qb->orderBy('r.dateReponse', 'DESC');

        $reponses = $qb->getQuery()->getResult();

        return $this->render('admin/reponse/index.html.twig', [
            'reponses' => $reponses,
            'filters' => [
                'contenu' => $contenu,
                'date' => $date,
            ]
        ]);
    }

    #[Route('/new/{reclamationId}', name: 'admin_reponse_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $reclamationId): Response
    {
        $reclamation = $this->em->getRepository(Reclamation::class)->find($reclamationId);
        
        if (!$reclamation) {
            throw $this->createNotFoundException('Réclamation non trouvée');
        }

        $reponse = new Reponse();
        $reponse->setReclamation($reclamation);

        if ($request->isMethod('POST')) {
            $contenu = trim($request->request->get('contenu', ''));
            $contenu = strip_tags($contenu);

            $reponse->setContenu($contenu);
            $reponse->setUser($this->getUser());

            $this->em->persist($reponse);
            $this->em->flush();

            return $this->redirectToRoute('admin_reclamation_show', ['id' => $reclamation->getId()]);
        }

        return $this->render('admin/reponse/new.html.twig', [
            'reponse' => $reponse,
            'reclamation' => $reclamation,
        ]);
    }

    #[Route('/{id}', name: 'admin_reponse_show', methods: ['GET'])]
    public function show(Reponse $reponse): Response
    {
        return $this->render('admin/reponse/show.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_reponse_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Reponse $reponse): Response
    {
        if ($request->isMethod('POST')) {
            $contenu = trim($request->request->get('contenu', ''));
            $contenu = strip_tags($contenu);

            $reponse->setContenu($contenu);

            $this->em->flush();

            return $this->redirectToRoute('admin_reclamation_show', ['id' => $reponse->getReclamation()->getId()]);
        }

        return $this->render('admin/reponse/edit.html.twig', [
            'reponse' => $reponse,
        ]);
    }

    #[Route('/{id}', name: 'admin_reponse_delete', methods: ['POST'])]
    public function delete(Request $request, Reponse $reponse): Response
    {
        $reclamationId = $reponse->getReclamation()->getId();

        if ($this->isCsrfTokenValid('delete' . $reponse->getId(), $request->request->get('_token'))) {
            $this->em->remove($reponse);
            $this->em->flush();
        }

        return $this->redirectToRoute('admin_reclamation_show', ['id' => $reclamationId]);
    }
}
