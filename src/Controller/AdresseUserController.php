<?php

namespace App\Controller;

use App\Entity\AdresseUser;
use App\Form\AdresseUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/adresse_user')]
final class AdresseUserController extends AbstractController
{
    #[Route(name: 'app_adresse_user_index', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $adresseUsers = $entityManager
            ->getRepository(AdresseUser::class)
            ->findAll();

        return $this->render('adresse_user/index.html.twig', [
            'adresse_users' => $adresseUsers,
        ]);
    }

    #[Route('/new', name: 'app_adresse_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $adresseUser = new AdresseUser();
        $form = $this->createForm(AdresseUserType::class, $adresseUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($adresseUser);
            $entityManager->flush();

            return $this->redirectToRoute('app_adresse_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('adresse_user/new.html.twig', [
            'adresse_user' => $adresseUser,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_adresse_user_show', methods: ['GET'])]
    public function show(AdresseUser $adresseUser): Response
    {
        return $this->render('adresse_user/show.html.twig', [
            'adresse_user' => $adresseUser,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_adresse_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, AdresseUser $adresseUser, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(AdresseUserType::class, $adresseUser);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_adresse_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('adresse_user/edit.html.twig', [
            'adresse_user' => $adresseUser,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_adresse_user_delete', methods: ['POST'])]
    public function delete(Request $request, AdresseUser $adresseUser, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$adresseUser->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($adresseUser);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_adresse_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
