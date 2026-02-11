<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Dompdf\Dompdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('commande')]
class CommandeController extends AbstractController
{
    #[Route('', name: 'app_commande_index', methods: ['GET'])]
    public function index(Request $request, CommandeRepository $commandeRepository): Response
    {
        $id = $request->query->getInt('id') ?: null;
        $statut = $request->query->get('statut') ?: null;
        $all = $request->query->getBoolean('all');
        $sort = $request->query->get('sort');
        $direction = strtoupper($request->query->get('direction', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        // Allowed sort columns mapping
        $allowedSorts = [
            'id' => 'c.id',
            'utilisateur' => 'u.email',
            'totales' => 'c.totales',
            'statut' => 'c.statut',
            'created_at' => 'c.created_at',
        ];

        // If ID provided, return single result (no sorting)
        if ($id !== null) {
            $found = $commandeRepository->find($id);
            $commandes = $found ? [$found] : [];
        } else {
            // Build query with optional statut filter and sorting
            $qb = $commandeRepository->createQueryBuilder('c')
                ->leftJoin('c.utilisateur', 'u')
                ->addSelect('u');

            if ($statut) {
                $qb->andWhere('c.statut = :statut')
                   ->setParameter('statut', $statut);
            }

            if ($sort && isset($allowedSorts[$sort])) {
                $qb->orderBy($allowedSorts[$sort], $direction);
            } else {
                // Fetch all results first (without custom sort), then sort in PHP
                $qb->orderBy('c.created_at', 'DESC');
            }

            if ($all) {
                // no limit
            } else {
                // default recent limit when not requesting all
                $qb->setMaxResults(100);
            }

            $commandes = $qb->getQuery()->getResult();
        }

        return $this->render('commande/index.html.twig', [
            'commandes' => $commandes,
            'filter_id' => $id,
            'filter_statut' => $statut,
            'filter_all' => $all,
            'sort' => $sort,
            'direction' => $direction,
            'statistics' => $commandeRepository->getStatistics(),
        ]);
    }

    #[Route('/{id}', name: 'app_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('new', name: 'app_commande_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commande->setCreatedAt(new \DateTime());
            $entityManager->persist($commande);
            $entityManager->flush();

            $this->addFlash('success', 'Commande créée avec succès!');

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Commande modifiée avec succès!');

            return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();

            $this->addFlash('success', 'Commande supprimée avec succès!');
        }

        return $this->redirectToRoute('app_commande_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/export/pdf', name: 'app_commande_export_pdf', methods: ['GET'])]
    public function exportPdf(Request $request, CommandeRepository $commandeRepository): Response
    {
        $id = $request->query->getInt('id') ?: null;
        $statut = $request->query->get('statut') ?: null;
        $all = $request->query->getBoolean('all');
        $sort = $request->query->get('sort');
        $direction = strtoupper($request->query->get('direction', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        // Allowed sort columns mapping
        $allowedSorts = [
            'id' => 'c.id',
            'utilisateur' => 'u.email',
            'totales' => 'c.totales',
            'statut' => 'c.statut',
            'created_at' => 'c.created_at',
        ];

        // If ID provided, return single result
        if ($id !== null) {
            $found = $commandeRepository->find($id);
            $commandes = $found ? [$found] : [];
        } else {
            // Build query with optional statut filter and sorting
            $qb = $commandeRepository->createQueryBuilder('c')
                ->leftJoin('c.utilisateur', 'u')
                ->addSelect('u');

            if ($statut) {
                $qb->andWhere('c.statut = :statut')
                   ->setParameter('statut', $statut);
            }

            if ($sort && isset($allowedSorts[$sort])) {
                $qb->orderBy($allowedSorts[$sort], $direction);
            } else {
                $qb->orderBy('c.created_at', 'DESC');
            }

            if (!$all) {
                $qb->setMaxResults(100);
            }

            $commandes = $qb->getQuery()->getResult();
        }

        // Render HTML content for PDF
        $html = $this->renderView('commande/export-pdf.html.twig', [
            'commandes' => $commandes,
        ]);

        // Generate PDF
        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return new Response(
            $dompdf->output(),
            Response::HTTP_OK,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="commandes_' . date('Y-m-d_H-i-s') . '.pdf"',
            ]
        );
    }
}
