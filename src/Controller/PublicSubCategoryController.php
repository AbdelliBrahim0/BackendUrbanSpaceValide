<?php

namespace App\Controller;

use App\Entity\SubCategory;
use App\Repository\SubCategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/public/subcategories')]
class PublicSubCategoryController extends AbstractController
{
    #[Route('', name: 'api_public_subcategories_list', methods: ['GET'])]
    public function list(SubCategoryRepository $repo): JsonResponse
    {
        $subCategories = $repo->findBy([], ['name' => 'ASC']);
        $data = array_map(function (SubCategory $sc) {
            return [
                'id' => $sc->getId(),
                'name' => $sc->getName(),
                'categories' => array_map(fn($c) => [
                    'id' => $c->getId(),
                    'name' => $c->getName()
                ], $sc->getCategories()->toArray()),
                'createdAt' => $sc->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $subCategories);

        return new JsonResponse(['success' => true, 'items' => $data], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_public_subcategories_get', methods: ['GET'])]
    public function getOne(SubCategory $subCategory): JsonResponse
    {
        $data = [
            'id' => $subCategory->getId(),
            'name' => $subCategory->getName(),
            'categories' => array_map(fn($c) => [
                'id' => $c->getId(),
                'name' => $c->getName()
            ], $subCategory->getCategories()->toArray()),
            'createdAt' => $subCategory->getCreatedAt()->format('Y-m-d H:i:s'),
        ];

        return new JsonResponse(['success' => true, 'item' => $data], Response::HTTP_OK);
    }
}
