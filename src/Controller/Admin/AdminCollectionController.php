<?php

namespace App\Controller\Admin;

use App\Entity\ProductCollection;
use App\Form\CollectionType;
use App\Repository\ProductCollectionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/collections')]
#[IsGranted('ROLE_ADMIN')]
class AdminCollectionController extends AbstractController
{
    #[Route('', name: 'admin_collections_index', methods: ['GET'])]
    public function index(ProductCollectionRepository $collectionRepository): Response
    {
        return $this->render('admin/collection/index.html.twig', [
            'collections' => $collectionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_collections_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $collection = new ProductCollection();
        $form = $this->createForm(CollectionType::class, $collection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($collection);
            $entityManager->flush();

            $this->addFlash('success', 'La collection a été créée avec succès.');
            return $this->redirectToRoute('admin_collections_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/collection/new.html.twig', [
            'collection' => $collection,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_collections_show', methods: ['GET'])]
    public function show(ProductCollection $collection): Response
    {
        return $this->render('admin/collection/show.html.twig', [
            'collection' => $collection,
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_collections_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ProductCollection $collection, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CollectionType::class, $collection);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'La collection a été mise à jour avec succès.');
            return $this->redirectToRoute('admin_collections_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/collection/edit.html.twig', [
            'collection' => $collection,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'admin_collections_delete', methods: ['POST'])]
    public function delete(Request $request, ProductCollection $collection, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$collection->getId(), $request->request->get('_token'))) {
            $entityManager->remove($collection);
            $entityManager->flush();
            $this->addFlash('success', 'La collection a été supprimée avec succès.');
        } else {
            $this->addFlash('error', 'Token CSRF invalide. La suppression a échoué.');
        }

        return $this->redirectToRoute('admin_collections_index', [], Response::HTTP_SEE_OTHER);
    }
}
