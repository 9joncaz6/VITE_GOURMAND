<?php

namespace App\EventListener;

use Symfony\Component\Security\Http\Event\LogoutEvent;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Session\Session;

class LogoutListener
{
    public function __construct(private UrlGeneratorInterface $urlGenerator) {}

    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();
        $session = $request->getSession();

        // Vérification pour éviter le soulignement IDE
        if ($session instanceof Session) {
            $session->getFlashBag()->add('success', 'Vous avez été déconnecté avec succès.');
        }

        // Redirection après déconnexion
        $event->setResponse(
            new RedirectResponse($this->urlGenerator->generate('app_home'))
        );
    }
}
