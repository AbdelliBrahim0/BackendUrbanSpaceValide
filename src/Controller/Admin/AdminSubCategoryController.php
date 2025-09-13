<?php

namespace App\Controller\Admin;

use App\Entity\SubCategory;
use App\Form\SubCategoryType;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/admin/subcategories')]
#[IsGranted('ROLE_ADMIN')]
class AdminSubCategoryController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SubCategoryRepository $subCategoryRepository
    ) {
    }

    #[Route('', name: 'admin_subcategories_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('admin/subcategory/index.html.twig', [
            'subcategories' => $this->subCategoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'admin_subcategories_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $subCategory = new SubCategory();
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($subCategory);
            $this->em->flush();

            $this->addFlash('success', 'La sous-catégorie a été créée avec succès.');
            return $this->redirectToRoute('admin_subcategories_index');
        }

        return $this->render('admin/subcategory/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'admin_subcategories_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SubCategory $subCategory): Response
    {
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            $this->addFlash('success', 'La sous-catégorie a été mise à jour avec succès.');
            return $this->redirectToRoute('admin_subcategories_index');
        }

        return $this->render('admin/subcategory/edit.html.twig', [
            'subcategory' => $subCategory,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'admin_subcategories_delete', methods: ['POST'])]
    public function delete(Request $request, SubCategory $subCategory): Response
    {
        if ($this->isCsrfTokenValid('delete'.$subCategory->getId(), $request->request->get('_token'))) {
            $this->em->remove($subCategory);
            $this->em->flush();
            $this->addFlash('success', 'La sous-catégorie a été supprimée avec succès.');
        }

        return $this->redirectToRoute('admin_subcategories_index');
    }
}
