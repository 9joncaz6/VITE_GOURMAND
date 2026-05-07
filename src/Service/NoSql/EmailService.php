<?php

namespace App\Service\NoSQL;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig
    ) {}

    public function sendConfirmationCommande($user, $commande)
    {
        $html = $this->twig->render('emails/confirmation_commande.html.twig', [
            'user' => $user,
            'commande' => $commande,
            'items' => $commande->getItems(),
        ]);

        $email = (new Email())
            ->from('no-reply@tonsite.com')
            ->to($user->getEmail())
            ->subject('Confirmation de votre commande #' . $commande->getId())
            ->html($html);

        $this->mailer->send($email);
    }
}