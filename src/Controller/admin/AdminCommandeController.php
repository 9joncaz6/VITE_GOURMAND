<?php

namespace App\Controller\admin;

use App\Entity\Commande;
use App\Entity\CommandeStatut;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Mailer\MailerInterface; // ✅ IMPORTANT

#[Route('/admin/commandes')]
#[IsGranted('ROLE_ADMIN')]
class AdminCommandeController extends AbstractController
{
    #[Route('', name: 'admin_commandes_index')]
    public function index(Request $request, CommandeRepository $repo): Response
    {
        $statutFiltre = $request->query->get('statut');

        if ($statutFiltre) {
            $commandes = $repo->findByStatutActuel($statutFiltre);
        } else {
            $commandes = $repo->findAllOrdered();
        }

        return $this->render('admin/commandes/index.html.twig', [
            'commandes' => $commandes,
            'statutFiltre' => $statutFiltre,
            'countEnAttente' => $repo->countByStatut('en_attente'),
            'countEnPreparation' => $repo->countByStatut('en_preparation'),
            'countTerminees' => $repo->countByStatut('terminee'),
        ]);
    }

    #[Route('/{id}', name: 'admin_commandes_show')]
    public function show(Commande $commande): Response
    {
        return $this->render('admin/commandes/show.html.twig', [
            'commande' => $commande,
        ]);
    }

    #[Route('/{id}/statut', name: 'admin_commandes_statut', methods: ['POST'])]
    public function changeStatut(
        Request $request,
        Commande $commande,
        EntityManagerInterface $em,
        MailerInterface $mailer // ✅ OK
    ): Response {
        $nouveauStatut = $request->request->get('statut');
        $commentaire = $request->request->get('commentaire');

        if (!$nouveauStatut) {
            $this->addFlash('error', 'Aucun statut sélectionné.');
            return $this->redirectToRoute('admin_commandes_show', ['id' => $commande->getId()]);
        }

        // Historique
        $statut = new CommandeStatut();
        $statut->setCommande($commande);
        $statut->setStatut($nouveauStatut);
        $statut->setCommentaire($commentaire);
        $statut->setDateMaj(new \DateTimeImmutable());

        // Mise à jour du statut fallback
        $commande->setStatus($nouveauStatut);

        $em->persist($statut);
        $em->flush();

        // Email si terminée
        if ($nouveauStatut === 'terminee') {
            $email = (new \Symfony\Component\Mime\Email())
                ->from('no-reply@vitegourmand.fr')
                ->to($commande->getUtilisateur()->getEmail())
                ->subject('Votre commande est terminée')
                ->html($this->renderView('emails/commande_terminee.html.twig', [
                    'commande' => $commande,
                    'user' => $commande->getUtilisateur(),
                ]));

            $mailer->send($email);
        }

        $this->addFlash('success', 'Statut mis à jour.');

        return $this->redirectToRoute('admin_commandes_index');
    }
}
