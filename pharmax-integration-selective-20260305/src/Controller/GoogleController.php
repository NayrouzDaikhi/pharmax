<?php

namespace App\Controller;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GoogleController extends AbstractController
{
    #[Route('/connect/google', name: 'connect_google_start')]
    public function connectAction(ClientRegistry $clientRegistry): RedirectResponse
    {
        return $clientRegistry
            ->getClient('google')
            ->redirect(['email', 'profile']);
    }

    /**
     * Shows the redirect URI this app sends to Google (same URL the OAuth client uses).
     * Add this exact value to Google Cloud Console → Credentials → Authorized redirect URIs.
     */
    #[Route('/connect/google/redirect-uri', name: 'connect_google_redirect_uri', methods: ['GET'])]
    public function redirectUriAction(): Response
    {
        $redirectUri = $this->generateUrl(
            'connect_google_check',
            [],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        return new Response(
            $redirectUri,
            Response::HTTP_OK,
            ['Content-Type' => 'text/plain; charset=UTF-8']
        );
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function connectCheckAction(): void
    {
        // This will be handled by GoogleAuthenticator
        // If we get here, authentication failed
        throw new \Exception('This should not be reached');
    }
}
