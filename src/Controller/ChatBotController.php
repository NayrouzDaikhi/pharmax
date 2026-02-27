<?php

namespace App\Controller;

use App\Service\ChatBotService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/chatbot', name: 'chatbot_')]
class ChatBotController extends AbstractController
{
    public function __construct(
        private ChatBotService $chatBotService,
    ) {}

    /**
     * Afficher l'interface du chatbot
     * GET /chatbot
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('chatbot/index.html.twig');
    }
}

