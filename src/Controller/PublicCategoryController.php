<?php

namespace App\Controller;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/public/categories')]
class PublicCategoryController extends AbstractController
{
    #[Route('', name: 'api_public_categories_list', methods: ['GET'])]
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
                'createdAt' => $c->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $categories);

        return new JsonResponse(['success' => true, 'items' => $data], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_public_categories_get', methods: ['GET'])]
    public function getOne(Category $category): JsonResponse
    {
        $data = [
            'id' => $category->getId(),
            'name' => $category->getName(),
            'subCategories' => array_map(fn($sc) => [
                'id' => $sc->getId(),
                'name' => $sc->getName()
            ], $category->getSubCategories()->toArray()),
            'createdAt' => $category->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['success' => true, 'item' => $data], Response::HTTP_OK);
    }
}
