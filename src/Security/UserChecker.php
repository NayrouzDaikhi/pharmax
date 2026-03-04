<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;

class UserChecker implements UserCheckerInterface
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->isBlocked()) {
            // Will be shown as authentication error
            throw new CustomUserMessageAccountStatusException('Your account has been blocked. Please contact support.');
        }

        $request = $this->requestStack->getCurrentRequest();
        
        // Skip face auth check for OAuth logins if desired (e.g., check if a specific route)
        if ($request && $request->attributes->get('_route') === 'connect_google_check') {
            return;
        }

        if ($request) {
            // Only require face token if the user has registered facial data
            if ($user->getDataFaceApi()) {
                // Require the token from the client request
                $clientToken = $request->get('tokenFaceRecognition'); // Make sure your frontend sends this with the password!
                
                // Get the token we stored securely on the server session during `/api/face-recognition`
                $sessionToken = $this->requestStack->getSession()->get('tokenFaceRecognition');

                if (!$clientToken || !$sessionToken || $clientToken !== $sessionToken) {
                    throw new CustomUserMessageAccountStatusException("Facial recognition verification required or invalid.");
                }
            }
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        // Clean up the session token after successful login to prevent reuse
        if ($this->requestStack->getSession()->has('tokenFaceRecognition')) {
            $this->requestStack->getSession()->remove('tokenFaceRecognition');
        }
    }
}
