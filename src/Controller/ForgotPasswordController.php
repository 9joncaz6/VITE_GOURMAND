<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Form\ForgotPasswordType;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class ForgotPasswordController extends AbstractController
{
    #[Route('/mot-de-passe-oublie', name: 'app_forgot_password')]
public function forgotPassword(
    Request $request,
    EntityManagerInterface $em,
    EmailService $emailService
): Response {

    if ($request->isMethod('POST')) {

        $email = $request->request->get('email');
        $user = $em->getRepository(Utilisateur::class)->findOneBy(['email' => $email]);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $user->setResetToken($token);
            $em->flush();

            $emailService->sendResetPasswordEmail($user);
        }

        $this->addFlash('success', 'Si un compte existe avec cet email, un lien de réinitialisation vous a été envoyé.');
        return $this->redirectToRoute('app_login');
    }

    return $this->render('security/forgot_password.html.twig');
}


    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function resetPassword(
        string $token,
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher
    ): Response {
        $user = $em->getRepository(Utilisateur::class)->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Lien invalide ou expiré.');
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');

            $hashed = $passwordHasher->hashPassword($user, $newPassword);
            $user->setPassword($hashed);
            $user->setResetToken(null);

            $em->flush();

            $this->addFlash('success', 'Votre mot de passe a été réinitialisé.');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'token' => $token,
        ]);
    }
}
