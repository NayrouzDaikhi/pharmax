<?php

namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class EntityNotFoundListener implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['onKernelException', 10],
        ];
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        // Capture l'erreur EntityValueResolver pour Commande
        if (strpos($exception->getMessage(), 'App\Entity\Commande') !== false) {
            // Convertir en NotFoundHttpException (404)
            $httpException = new NotFoundHttpException(
                'La commande demandée n\'existe pas.',
                $exception
            );
            $event->setThrowable($httpException);
        }
    }
}

