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
    #[Route('/accueil', name: 'accueil')]
    #[Route('/acceuil', name: 'acceuil')]
    public function index(
        ProduitRepository $produitRepository,
        CategorieRepository $categorieRepository,
        ArticleRepository $articleRepository
    ): Response
    {
        // Get 10 most recent products
        $recentProduits = $produitRepository->findBy([], ['id' => 'DESC'], 10);
        
        // Get recent articles sorted by date creation descending
        $recentArticles = $articleRepository->findBy([], ['date_creation' => 'DESC'], 6);
        
        // Get top 6 popular products (just use recent for now - no N+1 queries)
        $populaireProduits = $produitRepository->findBy(['statut' => true], ['id' => 'DESC'], 6);
        
        // Get top 4 categories with product counts (simplified - no loop queries)
        $allCategories = $categorieRepository->findAll();
        $topCategories = array_slice($allCategories, 0, 4);
        
        // Format categories for template
        $topCategoriesFormatted = array_map(function($categorie) {
            return [
                'categorie' => $categorie,
                'count' => 0  // Placeholder count - not queried on homepage
            ];
        }, $topCategories);
        
        $stats = [
            'total_produits' => $produitRepository->countTotal(),
            'total_categories' => $categorieRepository->countTotal(),
            'produits_valables' => $produitRepository->countAvailable(),
            'recent_products' => $recentProduits,
            'recent_articles' => $recentArticles,
            'popular_products' => $populaireProduits,
            'top_categories' => $topCategoriesFormatted,
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

    // ❌ ROUTE DISABLED - Use app_front_produits from BlogController instead
    // #[Route('/produits', name: 'front_produits')]
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

    // ❌ ROUTE DISABLED - Use app_front_detail_produit from BlogController instead
    // #[Route('/produit/{id<\d+>}', name: 'front_detail')]
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
