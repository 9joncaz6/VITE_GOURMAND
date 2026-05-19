<?php

namespace App\Controller\admin;

use App\Entity\Utilisateur;
use App\Form\EmployeType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[Route('/admin/employes')]
class AdminEmployeController extends AbstractController
{
    #[Route('/', name: 'admin_employes_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $employes = $em->getRepository(Utilisateur::class)->findAll();

        return $this->render('admin/employe/index.html.twig', [
            'employes' => $employes,
        ]);
    }

    #[Route('/new', name: 'admin_employes_new')]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        MailerInterface $mailer
    ): Response {
        $employe = new Utilisateur();

        $form = $this->createForm(EmployeType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Mot de passe généré automatiquement (16 caractères)
            $plainPassword = bin2hex(random_bytes(8));
            $hashed = $hasher->hashPassword($employe, $plainPassword);
            $employe->setPassword($hashed);

            // Rôle employé par défaut
            if (empty($employe->getRoles()) || $employe->getRoles() === ['ROLE_USER']) {
                $employe->setRoles(['ROLE_EMPLOYE']);
            }

            // Actif par défaut
            if ($employe->getActif() === null) {
                $employe->setActif(true);
            }

            $em->persist($employe);
            $em->flush();

            // Envoi du mail
            $email = (new Email())
                ->from('admin@v-g.fr')
                ->to($employe->getEmail())
                ->subject('Votre compte employé a été créé')
                ->text(
                    "Bonjour,\nVotre compte employé a été créé.\n".
                    "Identifiant : ".$employe->getEmail()."\n".
                    "Mot de passe : ".$plainPassword
                );

            $mailer->send($email);

            $this->addFlash('success', 'Employé créé et email envoyé.');
            return $this->redirectToRoute('admin_employes_index');
        }

        return $this->render('admin/employe/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'admin_employe_edit')]
    public function edit(
        Utilisateur $employe,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        $form = $this->createForm(EmployeType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->flush();

            $this->addFlash('success', 'Employé mis à jour avec succès.');
            return $this->redirectToRoute('admin_employes_index');
        }

        return $this->render('admin/employe/edit.html.twig', [
            'form' => $form->createView(),
            'employe' => $employe,
        ]);
    }

    #[Route('/delete/{id}', name: 'admin_employe_delete', methods: ['POST'])]
    public function delete(Request $request, Utilisateur $employe, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$employe->getId(), $request->request->get('_token'))) {
            $em->remove($employe);
            $em->flush();
            $this->addFlash('success', 'Employé supprimé.');
        }

        return $this->redirectToRoute('admin_employes_index');
    }
}
