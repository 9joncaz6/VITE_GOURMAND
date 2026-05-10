<?php

namespace App\Controller\admin;

use App\Entity\Plat;
use App\Form\PlatType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/plats')]
class PlatController extends AbstractController
{
    #[Route('/', name: 'app_plat_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $plats = $em->getRepository(Plat::class)->findAll();

        return $this->render('admin/plat/index.html.twig', [
            'plats' => $plats,
        ]);
    }

    #[Route('/new', name: 'app_plat_new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $plat = new Plat();
        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload image si fournie
            $image = $form->get('imageFile')->getData();
            if ($image) {
                $filename = uniqid('plat_') . '.' . $image->guessExtension();
                $image->move('uploads/plats', $filename);
                $plat->setImage($filename);
            }

            $em->persist($plat);
            $em->flush();

            // ⭐ Retour automatique vers le menu si paramètre "menu" présent
            if ($request->query->get('menu')) {
                return $this->redirectToRoute('admin_menu_edit', [
                    'id' => $request->query->get('menu')
                ]);
            }

            // Sinon retour normal vers la liste des plats
            return $this->redirectToRoute('app_plat_index');
        }

        return $this->render('admin/plat/create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/edit/{id}', name: 'app_plat_edit')]
    public function edit(Plat $plat, Request $request, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $form = $this->createForm(PlatType::class, $plat);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Upload image si nouvelle image
            $image = $form->get('imageFile')->getData();
            if ($image) {
                $filename = uniqid('plat_') . '.' . $image->guessExtension();
                $image->move('uploads/plats', $filename);
                $plat->setImage($filename);
            }

            $em->flush();

            return $this->redirectToRoute('app_plat_index');
        }

        return $this->render('admin/plat/edit.html.twig', [
            'form' => $form->createView(),
            'plat' => $plat,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_plat_delete')]
    public function delete(Plat $plat, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_EMPLOYE');

        $em->remove($plat);
        $em->flush();

        return $this->redirectToRoute('app_plat_index');
    }
}
