<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de test pour les traductions
 */
#[Route('/test/translate')]
class TestTranslateController extends AbstractController
{
    /**
     * Page de test simple pour tester la traduction
     * Accès: http://localhost:8000/test/translate
     */
    #[Route('', name: 'test_translate_page', methods: ['GET'])]
    public function testPage(): Response
    {
        return $this->render('test/translate.html.twig');
    }
}
