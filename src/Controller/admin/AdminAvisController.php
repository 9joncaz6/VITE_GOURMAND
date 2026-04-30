<?php

namespace App\Controller\admin;

use App\Entity\Avis;
use App\Repository\AvisRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/avis')]
class AdminAvisController extends AbstractController
{
    #[Route('/', name: 'admin_avis_index')]
    public function index(Request $request, AvisRepository $repo, MenuRepository $menuRepo): Response
    {
        $note = $request->query->get('note');
        $menuId = $request->query->get('menu');

        // Convertir en int ou null
        $note = $note !== null && $note !== '' ? (int)$note : null;
        $menuId = $menuId !== null && $menuId !== '' ? (int)$menuId : null;

        return $this->render('admin/avis/index.html.twig', [
            'avis' => $repo->searchAvis($note, $menuId),
            'menus' => $menuRepo->findAll(),
            'noteSelected' => $note,
            'menuSelected' => $menuId
        ]);
    }

    #[Route('/{id}', name: 'admin_avis_show')]
    public function show(Avis $avis): Response
    {
        return $this->render('admin/avis/show.html.twig', [
            'avis' => $avis
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_avis_delete')]
    public function delete(Avis $avis, EntityManagerInterface $em): Response
    {
        $em->remove($avis);
        $em->flush();

        $this->addFlash('success', 'Avis supprimé.');
        return $this->redirectToRoute('admin_avis_index');
    }

    #[Route('/{id}/toggle', name: 'admin_avis_toggle')]
    public function toggle(Avis $avis, EntityManagerInterface $em): Response
    {
        $avis->setValide(!$avis->isValide());
        $em->flush();

        $this->addFlash('success', 'Statut de l\'avis mis à jour.');
        return $this->redirectToRoute('admin_avis_index');
    }
}
