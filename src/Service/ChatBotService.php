<?php

namespace App\Service;

use Exception;

class ChatBotService
{
    public function __construct(
        private OllamaService $ollamaService,
        private ArticleSearchService $articleSearchService,
        private ?GoogleTranslationService $translationService = null,
    ) {
        error_log('[ChatBotService] Constructor: OllamaService=' . ($ollamaService ? 'OK' : 'NULL') . 
                  ', ArticleSearchService=' . ($articleSearchService ? 'OK' : 'NULL') .
                  ', GoogleTranslationService=' . ($this->translationService ? 'OK' : 'NULL'));
    }

    /**
     * Répondre à une question en utilisant les articles de la base de données
     */
    public function answerQuestion(string $question, ?int $articleId = null, ?string $articleTitle = null): array
    {
        try {
            // Valider la question
            if (empty(trim($question))) {
                throw new Exception('La question ne peut pas être vide.');
            }

            if (mb_strlen($question) > 1000) {
                throw new Exception('La question est trop longue (maximum 1000 caractères).');
            }

            // Détection de langue désactivée pour l'instant
            $requestedLanguage = null;
            
            // Initialiser le tableau des articles
            $articles = [];
            $mainArticle = null;

            // SI UN ARTICLE ID EST FOURNI, LE RÉCUPÉRER DIRECTEMENT
            if ($articleId) {
                try {
                    $mainArticle = $this->articleSearchService->getArticleById($articleId);
                    if ($mainArticle) {
                        $articles[] = $mainArticle;
                        error_log('ChatBotService: Article ID ' . $articleId . ' récupéré directement');
                    } else {
                        error_log('ChatBotService: Article ID ' . $articleId . ' non trouvé');
                    }
                } catch (\Exception $e) {
                    error_log('ChatBotService: Erreur lors de la récupération de l\'article ID ' . $articleId . ': ' . $e->getMessage());
                }
            }

            // CHERCHER LES ARTICLES PERTINENTS PAR MOT-CLÉ (MÊME SI ON A UN ARTICLE PRINCIPAL)
            try {
                $relatedArticles = $this->articleSearchService->searchRelevantArticles($question, 3);
                // Ajouter les articles connexes qui ne sont pas l'article principal
                foreach ($relatedArticles as $article) {
                    if (!$mainArticle || $article->getId() !== $mainArticle->getId()) {
                        $articles[] = $article;
                    }
                }
            } catch (\Exception $e) {
                // Si la recherche échoue, continuer avec ce qu'on a
                error_log('ArticleSearchService error: ' . $e->getMessage());
            }
            
            if (empty($articles)) {
                return [
                    'success' => false,
                    'error' => 'Aucun article pertinent trouvé dans la base de données.',
                    'answer' => null,
                ];
            }

            // Formatar los artículos como contexto
            try {
                $context = $this->articleSearchService->formatArticlesForAI($articles, $articleId, $requestedLanguage);
            } catch (\Exception $e) {
                error_log('formatArticlesForAI error: ' . $e->getMessage());
                return [
                    'success' => false,
                    'error' => 'Erreur lors du formatage du contexte: ' . $e->getMessage(),
                    'answer' => null,
                ];
            }

            if (empty($context)) {
                return [
                    'success' => false,
                    'error' => 'Impossible de formatter les articles en contexte.',
                    'answer' => null,
                ];
            }

            // Préparer le prompt pour Ollama (avec contexte d'article si fourni)
            $prompt = $this->buildPrompt($question, $context, $articleId, $articleTitle);

            // Envoyer la requête à Ollama
            try {
                $response = $this->ollamaService->generateChatbotAnswer(
                    $question,
                    $context,
                    $articleId,
                    $articleTitle
                );
            } catch (Exception $ollamaError) {
                error_log('ChatBotService: Ollama API failed: ' . $ollamaError->getMessage());
                
                $errorMsg = $ollamaError->getMessage();
                
                // Better error message for user-friendly display
                if (strpos($errorMsg, 'is not downloaded yet') !== false) {
                    $displayError = '⏳ Le modèle IA est en cours de téléchargement...' . "\n"
                                  . 'Veuillez patienter quelques minutes et réessayer. ' . "\n"
                                  . 'Détail technique: ' . $errorMsg;
                } else {
                    $displayError = '⚠️ Les services Ollama AI ne sont pas disponibles. ' .
                                  'Assurez-vous qu\'Ollama est en cours d\'exécution sur localhost:11434. ' .
                                  'Détail: ' . $errorMsg;
                }
                
                // Return error message - NO FALLBACK
                return [
                    'success' => false,
                    'error' => $displayError,
                    'answer' => null,
                ];
            }

            return [
                'success' => true,
                'answer' => $response,
                'sources' => array_map(function($article) {
                    return [
                        'title' => $article->getTitre(),
                        'id' => $article->getId(),
                    ];
                }, $articles),
            ];

        } catch (Exception $e) {
            error_log('ChatBotService::answerQuestion Exception: ' . $e->getMessage() . ' | File: ' . $e->getFile() . ':' . $e->getLine());
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'answer' => null,
            ];
        }
    }

    /**
     * Construire le prompt pour Gemini
     */
    private function buildPrompt(string $question, string $context, ?int $articleId = null, ?string $articleTitle = null): string
    {
        // Déterminer le contexte additionnel
        $articleContext = '';
        if ($articleId && $articleTitle) {
            $articleContext = "\n\n⭐ ARTICLE PRINCIPAL CONSULTÉ:\n";
            $articleContext .= "L'utilisateur consulte actuellement l'article: \"$articleTitle\"\n";
            $articleContext .= "Privilégie les informations de cet article dans ta réponse.";
        }

        $prompt = <<<PROMPT
Tu es un assistant IA intelligent pour une pharmacie en ligne appelée Pharmax. 
Ton rôle est d'aider les clients en leur fournissant des informations utiles et précises basées sur les articles de la base de données.

📚 ARTICLES DE RÉFÉRENCE (CONTEXTE):
$context
$articleContext

🎯 INSTRUCTIONS IMPORTANTES:
1. 🔍 ANALYSE LA QUESTION avec soin pour comprendre exactement ce que le client demande
2. 📖 UTILISE LES ARTICLES pour répondre - cherche les informations pertinentes dans le contexte fourni
3. ⭐ PRIVILÉGIE L'ARTICLE PRINCIPAL si l'utilisateur le consulte
4. 💬 SOIS CONVERSATIONNEL - réponds de manière amicale et engageante, pas de manière robotique
5. 🎯 SOIS SPÉCIFIQUE - adapte ta réponse à la question exacte posée, pas une réponse générique
6. 📝 STRUCTURE TA RÉPONSE avec des points clés ou des listes quand c'est approprié
7. ❓ SI TU NE SAIS PAS - dis clairement que tu n'as pas les informations, recommande un pharmacien
8. 🌍 UTILISE LE MÊME LANGAGE que la question (français ou anglais)
9. ✨ SOIS UTILE ET BIENVEILLANT - c'est un client qui demande de l'aide

⚠️ NE PAS:
- Ne repète pas la question
- Ne donne pas de réponses génériques
- Ne mélange pas des articles non pertinents
- Ne fais pas de diagnostic médical

QUESTION DU CLIENT:
"{$question}"

RÉPONSE PERSONNALISÉE:
PROMPT;

        return trim($prompt);
    }

    /**
     * Check if Ollama is configured and accessible
     */
    public function isApiKeyConfigured(): bool
    {
        return $this->ollamaService->isConfigured();
    }

    /**
     * Get Ollama status for health checks
     */
    public function getStatus(): array
    {
        return $this->ollamaService->getStatus();
    }

}
