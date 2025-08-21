<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/categories')]
#[IsGranted('ROLE_ADMIN')]
class CategoryController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'api_admin_categories_list', methods: ['GET'])]
    public function list(CategoryRepository $repo): JsonResponse
    {
        $categories = $repo->findBy([], ['name' => 'ASC']);
        $data = array_map(function (Category $c) {
            return [
                'id' => $c->getId(),
                'name' => $c->getName(),
                'subCategories' => array_map(fn($sc) => [
                    'id' => $sc->getId(),
                    'name' => $sc->getName()
                ], $c->getSubCategories()->toArray()),
                'products' => array_map(fn($p) => [
                    'id' => $p->getId(),
                    'name' => $p->getName()
                ], $c->getProducts()->toArray()),
                'createdAt' => $c->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $categories);

        return new JsonResponse(['success' => true, 'items' => $data], Response::HTTP_OK);
    }

    #[Route('', name: 'api_admin_categories_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->assertJson($request);
        $data = json_decode($request->getContent(), true);

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->submit([
            'name' => $data['name'] ?? null,
            'subCategories' => $data['subCategories'] ?? [],
            'products' => $data['products'] ?? [],
        ], false);

        if (!$form->isValid()) {
            return new JsonResponse([
                'success' => false,
                'errors' => $this->collectFormErrors($form),
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($category);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'item' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'subCategories' => array_map(fn($sc) => [
                    'id' => $sc->getId(),
                    'name' => $sc->getName()
                ], $category->getSubCategories()->toArray()),
                'products' => array_map(fn($p) => [
                    'id' => $p->getId(),
                    'name' => $p->getName()
                ], $category->getProducts()->toArray()),
                'createdAt' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
            ],
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_admin_categories_get', methods: ['GET'])]
    public function getOne(Category $category): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'item' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'subCategories' => array_map(fn($sc) => [
                    'id' => $sc->getId(),
                    'name' => $sc->getName()
                ], $category->getSubCategories()->toArray()),
                'products' => array_map(fn($p) => [
                    'id' => $p->getId(),
                    'name' => $p->getName()
                ], $category->getProducts()->toArray()),
                'createdAt' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
            ],
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_admin_categories_update', methods: ['PUT', 'PATCH'])]
    public function update(Category $category, Request $request): JsonResponse
    {
        $this->assertJson($request);
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(CategoryType::class, $category);
        $form->submit([
            'name' => $data['name'] ?? $category->getName(),
            'subCategories' => $data['subCategories'] ?? null,
            'products' => $data['products'] ?? null,
        ], false);

        if (!$form->isValid()) {
            return new JsonResponse([
                'success' => false,
                'errors' => $this->collectFormErrors($form),
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'item' => [
                'id' => $category->getId(),
                'name' => $category->getName(),
                'subCategories' => array_map(fn($sc) => [
                    'id' => $sc->getId(),
                    'name' => $sc->getName()
                ], $category->getSubCategories()->toArray()),
                'products' => array_map(fn($p) => [
                    'id' => $p->getId(),
                    'name' => $p->getName()
                ], $category->getProducts()->toArray()),
                'createdAt' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
            ],
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_admin_categories_delete', methods: ['DELETE'])]
    public function delete(Category $category): JsonResponse
    {
        $this->em->remove($category);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Catégorie supprimée avec succès.'
        ], Response::HTTP_OK);
    }

    private function assertJson(Request $request): void
    {
        $contentType = $request->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'application/json')) {
            throw $this->createNotFoundException('Content-Type doit être application/json');
        }
        $raw = $request->getContent();
        if ($raw !== '' && json_decode($raw, true) === null && json_last_error() !== JSON_ERROR_NONE) {
            throw $this->createNotFoundException('JSON invalide');
        }
    }

    private function collectFormErrors(\Symfony\Component\Form\FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return $errors;
    }
}
