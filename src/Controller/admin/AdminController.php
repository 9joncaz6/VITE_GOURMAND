<?php

namespace App\Controller\admin;

use App\Entity\Utilisateur;
use App\Form\CreateEmployeType;
use App\Repository\UtilisateurRepository;
use App\Repository\MenuRepository;
use App\Service\NoSQL\StatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('admin/dashboard.html.twig');
    }

    // ---------------------------------------------------------
    // LISTE DES EMPLOYÉS
    // ---------------------------------------------------------
    #[Route('/employes', name: 'admin_employe_list')]
    public function employeList(UtilisateurRepository $repo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $employes = $repo->findByRole('ROLE_EMPLOYE');

        return $this->render('admin/employe/index.html.twig', [
            'employes' => $employes,
        ]);
    }

    // ---------------------------------------------------------
    // CRÉATION D’UN EMPLOYÉ
    // ---------------------------------------------------------
    #[Route('/employes/create', name: 'admin_employe_create')]
    public function createEmploye(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher,
        MailerInterface $mailer
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $employe = new Utilisateur();
        $form = $this->createForm(CreateEmployeType::class, $employe);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Rôle employé obligatoire
            $employe->setRoles(['ROLE_EMPLOYE']);

            // Hash du mot de passe
            $hashed = $hasher->hashPassword($employe, $employe->getPassword());
            $employe->setPassword($hashed);

            // Actif par défaut
            $employe->setActif(true);

            $em->persist($employe);
            $em->flush();

            // Mail automatique
            $email = (new Email())
                ->from('contact@vitegourmand.fr')
                ->to($employe->getEmail())
                ->subject('Votre compte employé a été créé')
                ->text(
                    "Bonjour,\n\nVotre compte employé a été créé.\n".
                    "Veuillez contacter l’administrateur pour obtenir votre mot de passe.\n\n".
                    "Cordialement,\nL’équipe Vite & Gourmand"
                );

            $mailer->send($email);

            $this->addFlash('success', 'Employé créé avec succès.');
            return $this->redirectToRoute('admin_employe_list');
        }

        return $this->render('admin/employe/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ---------------------------------------------------------
    // ACTIVER / DÉSACTIVER UN EMPLOYÉ
    // ---------------------------------------------------------
    #[Route('/employes/{id}/toggle', name: 'admin_employe_toggle')]
    public function toggleEmploye(Utilisateur $employe, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Empêcher de désactiver un admin
        if (in_array('ROLE_ADMIN', $employe->getRoles())) {
            $this->addFlash('danger', 'Impossible de désactiver un administrateur.');
            return $this->redirectToRoute('admin_employe_list');
        }

        $employe->setActif(!$employe->isActif());
        $em->flush();

        $this->addFlash('success', 'Statut mis à jour.');
        return $this->redirectToRoute('admin_employe_list');
    }

    // ---------------------------------------------------------
    // STATISTIQUES (NoSQL)
    // ---------------------------------------------------------
    #[Route('/stats', name: 'admin_stats')]
    public function stats(StatsService $statsService, MenuRepository $menuRepo): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $stats = $statsService->getStats();
        $menus = $menuRepo->findAll();

        // Préparation des données pour Chart.js
        $labels = [];
        $commandes = [];
        $ca = [];

        foreach ($menus as $menu) {
            $labels[] = $menu->getTitre();
            $menuId = $menu->getId();

            $commandes[] = $stats['menus'][$menuId]['commandes'] ?? 0;
            $ca[] = $stats['menus'][$menuId]['ca'] ?? 0;
        }

        return $this->render('admin/stats.html.twig', [
            'labels' => json_encode($labels),
            'commandes' => json_encode($commandes),
            'ca' => json_encode($ca),
        ]);
    }

    #[Route('/employes/{id}/delete', name: 'admin_employe_delete', methods: ['POST'])]
public function deleteEmploye(Utilisateur $employe, EntityManagerInterface $em): Response
{
    $this->denyAccessUnlessGranted('ROLE_ADMIN');

    // Empêcher de supprimer un admin
    if (in_array('ROLE_ADMIN', $employe->getRoles())) {
        $this->addFlash('danger', 'Impossible de supprimer un administrateur.');
        return $this->redirectToRoute('admin_employe_list');
    }

    $em->remove($employe);
    $em->flush();

    $this->addFlash('success', 'Employé supprimé avec succès.');
    return $this->redirectToRoute('admin_employe_list');
}

}
