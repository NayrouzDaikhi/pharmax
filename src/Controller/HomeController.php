<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Repository\CategorieRepository;
use App\Repository\ArticleRepository;
use App\Repository\CommentaireRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(
        ProduitRepository $produitRepository,
        CategorieRepository $categorieRepository,
        ArticleRepository $articleRepository,
        CommentaireRepository $commentaireRepository
    ): Response
    {
        // Get 10 most recent products
        $recentProduits = $produitRepository->findBy([], ['id' => 'DESC'], 10);
        
        // Get recent articles sorted by date creation descending
        $recentArticles = $articleRepository->findBy([], ['date_creation' => 'DESC'], 10);
        
        // Get all available products and sort by comment count
        $allProduits = $produitRepository->findBy(['statut' => true]);
        
        // Sort by number of comments (most commented first)
        $produitsAvecCommentaires = [];
        foreach ($allProduits as $produit) {
            $commentCount = count($commentaireRepository->findBy(['produit' => $produit, 'statut' => 'valide']));
            $produitsAvecCommentaires[$produit->getId()] = [
                'produit' => $produit,
                'commentCount' => $commentCount
            ];
        }
        
        // Sort by comment count descending
        uasort($produitsAvecCommentaires, function($a, $b) {
            return $b['commentCount'] <=> $a['commentCount'];
        });
        
        $populaireProduits = array_slice(
            array_map(fn($item) => $item['produit'], $produitsAvecCommentaires),
            0,
            6
        );
        
        // Get featured categories (top 4)
        $allCategories = $categorieRepository->findAll();
        $categoriesAvecCompteur = [];
        
        foreach ($allCategories as $categorie) {
            $produitCount = count($produitRepository->findBy(['categorie' => $categorie]));
            $categoriesAvecCompteur[] = [
                'categorie' => $categorie,
                'count' => $produitCount
            ];
        }
        
        // Sort by product count descending
        usort($categoriesAvecCompteur, function($a, $b) {
            return $b['count'] <=> $a['count'];
        });
        
        $topCategories = array_slice($categoriesAvecCompteur, 0, 4);
        
        $stats = [
            'total_produits' => $produitRepository->countTotal(),
            'total_categories' => $categorieRepository->countTotal(),
            'produits_valables' => $produitRepository->countAvailable(),
            'recent_products' => $recentProduits,
            'recent_articles' => $recentArticles,
            'popular_products' => $populaireProduits,
            'top_categories' => $topCategories,
        ];

        return $this->render('front_home.html.twig', $stats);
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function dashboard(ProduitRepository $produitRepository, CategorieRepository $categorieRepository): Response
    {
        return $this->render('dashboard/sneat_dashboard.html.twig', [
            'totalProduits' => $produitRepository->countTotal(),
            'expiredProduits' => $produitRepository->countExpired(),
            'availableProduits' => $produitRepository->countAvailable(),
            'outOfStockProduits' => $produitRepository->countOutOfStock(),
            'mostExpensive' => $produitRepository->getMostExpensiveProducts(5),
            'leastExpensive' => $produitRepository->getLeastExpensiveProducts(5),
            'totalCategories' => $categorieRepository->countTotal(),
            'categories' => $categorieRepository->findAll(),
        ]);
    }

    #[Route('/produits', name: 'front_produits')]
    public function produits(Request $request, ProduitRepository $produitRepository, CategorieRepository $categorieRepository): Response
    {
        $search = $request->query->get('search', '');
        $categorie = $request->query->get('categorie', '');
        $sortBy = $request->query->get('sortBy', 'createdAt');
        $sortOrder = $request->query->get('sortOrder', 'DESC');

        $produits = $produitRepository->findByFilters($search, $categorie, $sortBy, $sortOrder);
        $categories = $categorieRepository->findAll();

        return $this->render('front_produits.html.twig', [
            'produits' => $produits,
            'categories' => $categories,
            'search' => $search,
            'categorie' => $categorie,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder,
        ]);
    }

    #[Route('/produit/{id<\d+>}', name: 'front_detail')]
    public function detail(int $id, ProduitRepository $produitRepository): Response
    {
        $produit = $produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }

        // Produits connexes de la même catégorie
        $related_products = [];
        if ($produit->getCategorie()) {
            $all_in_category = $produitRepository->findByFilters('', $produit->getCategorie()->getId());
            // Exclure le produit courant
            $related_products = array_filter($all_in_category, function($p) use ($id) {
                return $p->getId() !== $id;
            });
        }

        return $this->render('front_detail.html.twig', [
            'produit' => $produit,
            'related_products' => $related_products,
        ]);
    }

    #[Route('/admin', name: 'admin_dashboard')]
    public function oldDashboard(ProduitRepository $produitRepository, CategorieRepository $categorieRepository): Response
    {
        $statusByMonth = $produitRepository->getStatusByMonth();
        $mostExpensive = $produitRepository->getMostExpensiveProducts(5);
        $leastExpensive = $produitRepository->getLeastExpensiveProducts(5);

        $stats = [
            'total_produits' => $produitRepository->countTotal(),
            'total_categories' => $categorieRepository->countTotal(),
            'produits_expires' => $produitRepository->countExpired(),
            'produits_valables' => $produitRepository->countAvailable(),
            'produits_hors_stock' => $produitRepository->countOutOfStock(),
            'status_by_month' => $statusByMonth,
            'most_expensive' => $mostExpensive,
            'least_expensive' => $leastExpensive,
        ];

        return $this->redirectToRoute('app_dashboard');
    }
}
