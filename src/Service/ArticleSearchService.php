<?php

namespace App\Service;

use App\Repository\ArticleRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ArticleSearchService
{
    public function __construct(
        private ArticleRepository $articleRepository,
    ) {}

    /**
     * Récupérer un article spécifique par son ID
     */
    public function getArticleById(int $id): ?object
    {
        return $this->articleRepository->find($id);
    }

    /**
     * Chercher les articles pertinents par rapport à une question
     * Utilise la recherche par titre et contenu
     */
    public function searchRelevantArticles(string $query, int $limit = 5): array
    {
        // Nettoyer et normaliser la requête
        $searchTerms = $this->normalizeSearchTerms($query);
        
        if (empty($searchTerms)) {
            // Si pas de termes, retourner les articles récents
            return $this->articleRepository->findBy(
                [],
                ['created_at' => 'DESC'],
                $limit
            );
        }

        // Chercher dans les articles
        $articles = $this->articleRepository->searchByKeywords($searchTerms, $limit);

        return $articles;
    }

    /**
     * Normaliser les termes de recherche
     */
    private function normalizeSearchTerms(string $query): array
    {
        // Supprimer les accents et convertir en minuscules
        $query = mb_strtolower(trim($query));
        
        // Supprimer la ponctuation
        $query = preg_replace('/[^\w\s]/u', '', $query);
        
        // Diviser en mots individuels et filtrer les mots vides
        $terms = array_filter(
            explode(' ', $query),
            fn($term) => mb_strlen($term) > 2 // Minimum 3 caractères
        );

        return array_values($terms); // Réindexer
    }

    /**
     * Formater les articles pour l'envoi à l'IA (contexte)
     * Le premier article (s'il existe) sera considéré comme l'article principal
     * 
     * @param array $articles Articles à formater
     * @param int|null $mainArticleId ID de l'article principal
     * @param string|null $translationLanguage Code de la langue pour traduction (ex: 'ar' pour arabe)
     */
    public function formatArticlesForAI(array $articles, ?int $mainArticleId = null, ?string $translationLanguage = null): string
    {
        if (empty($articles)) {
            return "Pas d'articles disponibles dans la base de données.";
        }

        $context = "";
        $mainArticle = null;
        $otherArticles = [];

        // Séparer l'article principal des autres
        foreach ($articles as $article) {
            if ($mainArticleId && $article->getId() === $mainArticleId) {
                $mainArticle = $article;
            } else {
                $otherArticles[] = $article;
            }
        }

        // Formater l'article principal en premier (s'il existe)
        if ($mainArticle) {
            $context .= "=== ARTICLE PRINCIPAL (actuellement consulté) ===\n";
            $context .= "ID: " . $mainArticle->getId() . "\n";
            $context .= "Titre: " . $mainArticle->getTitre() . "\n";
            
            // Ajouter la date de publication
            if ($mainArticle->getDateCreation()) {
                $context .= "Date de publication: " . $mainArticle->getDateCreation()->format('d F Y \à H:i') . "\n";
            }
            
            $context .= "Contenu:\n" . $mainArticle->getContenu() . "\n";
            
            // Ajouter contenu en anglais si disponible
            if ($mainArticle->getContenuEn()) {
                $context .= "\n[English Version]\n" . $mainArticle->getContenuEn() . "\n";
            }
            
            // Ajouter contenu traduit si une langue est demandée
            if ($translationLanguage) {
                $context .= "\n[Translation Request: " . strtoupper($translationLanguage) . "]\n";
                $context .= "Note: Veuillez traduire le contenu ci-dessus en " . $translationLanguage . "\n";
            }
            
            $context .= "\n" . str_repeat("=", 50) . "\n\n";
        }

        // Ajouter les autres articles
        if (!empty($otherArticles)) {
            $context .= "=== ARTICLES CONNEXES ===\n\n";
            
            foreach ($otherArticles as $article) {
                $dateStr = $article->getDateCreation() ? $article->getDateCreation()->format('d F Y') : 'Date inconnue';
                
                $context .= sprintf(
                    "**%s** (ID: %d - Publié le: %s)\n%s\n",
                    $article->getTitre(),
                    $article->getId(),
                    $dateStr,
                    $article->getContenu()
                );
                
                if ($article->getContenuEn()) {
                    $context .= "[English Version]\n" . $article->getContenuEn() . "\n";
                }
                
                $context .= "\n";
            }
        }

        return $context;
    }

}
