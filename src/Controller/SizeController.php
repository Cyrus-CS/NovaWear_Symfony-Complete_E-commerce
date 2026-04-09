<?php

namespace App\Controller;

use App\Entity\Size;
use App\Form\SizeType;
use App\Repository\SizeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin/size')]
final class SizeController extends AbstractController
{
    #[Route(name: 'app_size_index', methods: ['GET'])]
    public function index(SizeRepository $sizeRepository): Response
    {
        return $this->render('admin/size/index.html.twig', [
            'sizes' => $sizeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_size_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $size = new Size();
        $form = $this->createForm(SizeType::class, $size);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($size);
            $entityManager->flush();

            return $this->redirectToRoute('app_size_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/size/new.html.twig', [
            'size' => $size,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_size_show', methods: ['GET'])]
    public function show(Size $size): Response
    {
        return $this->render('admin/size/show.html.twig', [
            'size' => $size,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_size_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Size $size, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SizeType::class, $size);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_size_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/size/edit.html.twig', [
            'size' => $size,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_size_delete', methods: ['POST'])]
    public function delete(Request $request, Size $size, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$size->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($size);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_size_index', [], Response::HTTP_SEE_OTHER);
    }
}
