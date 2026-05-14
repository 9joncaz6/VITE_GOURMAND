<?php

namespace App\Service;

use App\Entity\Utilisateur;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private Environment $twig,
        private RouterInterface $router
    ) {}

    public function sendConfirmationCommande($user, $commande): void
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

    public function sendContactMessage(string $nom, string $fromEmail, string $message): void
    {
        $emailMessage = (new Email())
            ->from($fromEmail)
            ->to('contact@vitegourmand.fr')
            ->subject('Nouveau message de contact')
            ->html("
                <p><strong>Nouveau message de contact :</strong></p>
                <p><strong>Nom :</strong> {$nom}</p>
                <p><strong>Email :</strong> {$fromEmail}</p>
                <p><strong>Message :</strong></p>
                <p>" . nl2br(htmlspecialchars($message)) . "</p>
            ");

        $this->mailer->send($emailMessage);
    }

    public function sendResetPasswordEmail(Utilisateur $user): void
    {
        // Génération automatique de l’URL ABSOLUE
        $resetLink = $this->router->generate(
            'app_reset_password',
            ['token' => $user->getResetToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new Email())
            ->from('no-reply@vitegourmand.fr')
            ->to($user->getEmail())
            ->subject('Réinitialisation de votre mot de passe')
            ->html("
                <p>Bonjour,</p>
                <p>Pour réinitialiser votre mot de passe, cliquez sur le lien ci-dessous :</p>
                <p><a href='{$resetLink}'>Réinitialiser mon mot de passe</a></p>
                <p>Si vous n'êtes pas à l'origine de cette demande, ignorez cet email.</p>
            ");

        $this->mailer->send($email);
    }
}
