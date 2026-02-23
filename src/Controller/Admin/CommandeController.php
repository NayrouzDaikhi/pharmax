<?php

namespace App\Controller\Admin;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/commandes')]
class CommandeController extends AbstractController
{
    #[Route('', name: 'app_admin_commande_index', methods: ['GET'])]
    public function index(Request $request, CommandeRepository $commandeRepository): Response
    {
        $id = $request->query->getInt('id') ?: null;
        $statut = $request->query->get('statut') ?: null;
        $all = $request->query->getBoolean('all');
        $sort = $request->query->get('sort');
        $direction = strtoupper($request->query->get('direction', 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

        $allowedSorts = [
            'id' => 'c.id',
            'utilisateur' => 'u.email',
            'totales' => 'c.totales',
            'statut' => 'c.statut',
            'created_at' => 'c.created_at',
        ];

        if ($id !== null) {
            $found = $commandeRepository->find($id);
            $commandes = $found ? [$found] : [];
        } else {
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

        return $this->render('admin/commande/index.html.twig', [
            'commandes' => $commandes,
            'filter_id' => $id,
            'filter_statut' => $statut,
            'filter_all' => $all,
            'sort' => $sort,
            'direction' => $direction,
            'statistics' => $commandeRepository->getStatistics(),
        ]);
    }

    #[Route('/{id}', name: 'app_admin_commande_show', methods: ['GET'])]
    public function show(Commande $commande): Response
    {
        return $this->render('admin/commande/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/new', name: 'app_admin_commande_new', methods: ['GET', 'POST'])]
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
            return $this->redirectToRoute('app_admin_commande_index');
        }

        return $this->render('admin/commande/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_commande_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Commande modifiée avec succès!');
            return $this->redirectToRoute('app_admin_commande_index');
        }

        return $this->render('admin/commande/edit.html.twig', [
            'commande' => $commande,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_admin_commande_delete', methods: ['POST'])]
    public function delete(Request $request, Commande $commande, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $commande->getId(), $request->request->get('_token'))) {
            $entityManager->remove($commande);
            $entityManager->flush();
            $this->addFlash('success', 'Commande supprimée avec succès!');
        }

        return $this->redirectToRoute('app_admin_commande_index');
    }
}
