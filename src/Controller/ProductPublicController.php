<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Category;
use App\Entity\SubCategory;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/products')]
class ProductPublicController extends AbstractController
{
    #[Route('', name: 'api_products_list', methods: ['GET'])]
    public function list(ProductRepository $repo, Request $request): JsonResponse
    {
        $categoryId = $request->query->get('category');
        $subCategoryId = $request->query->get('subcategory');

        $qb = $repo->createQueryBuilder('p')
            ->leftJoin('p.subCategories', 'sc')
            ->leftJoin('sc.categories', 'c1')
            ->leftJoin('p.categories', 'c2')
            ->addSelect('sc', 'c1', 'c2')
            ->orderBy('p.name', 'ASC');

        if ($categoryId) {
            $qb->andWhere('c1.id = :categoryId OR c2.id = :categoryId')
               ->setParameter('categoryId', $categoryId);
        }

        if ($subCategoryId) {
            $qb->andWhere('sc.id = :subCategoryId')
               ->setParameter('subCategoryId', $subCategoryId);
        }

        $products = $qb->getQuery()->getResult();

        $data = array_map([$this, 'serializeProduct'], $products);

        return new JsonResponse(['success' => true, 'items' => $data], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_products_get', methods: ['GET'])]
    public function getOne(Product $product): JsonResponse
    {
        return new JsonResponse([
            'success' => true,
            'item' => $this->serializeProduct($product)
        ]);
    }

    #[Route('/category/{id}', name: 'api_products_by_category', methods: ['GET'])]
    public function getByCategory(Category $category, ProductRepository $repo): JsonResponse
    {
        $products = $repo->createQueryBuilder('p')
            ->leftJoin('p.subCategories', 'sc')
            ->leftJoin('sc.categories', 'c1')
            ->leftJoin('p.categories', 'c2')
            ->where('c1.id = :categoryId OR c2.id = :categoryId')
            ->setParameter('categoryId', $category->getId())
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'success' => true,
            'category' => [
                'id' => $category->getId(),
                'name' => $category->getName()
            ],
            'items' => array_map([$this, 'serializeProduct'], $products)
        ]);
    }

    #[Route('/subcategory/{id}', name: 'api_products_by_subcategory', methods: ['GET'])]
    public function getBySubCategory(SubCategory $subCategory, ProductRepository $repo): JsonResponse
    {
        $products = $repo->createQueryBuilder('p')
            ->innerJoin('p.subCategories', 'sc')
            ->where('sc.id = :subCategoryId')
            ->setParameter('subCategoryId', $subCategory->getId())
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();

        return new JsonResponse([
            'success' => true,
            'subCategory' => [
                'id' => $subCategory->getId(),
                'name' => $subCategory->getName()
            ],
            'items' => array_map([$this, 'serializeProduct'], $products)
        ]);
    }

    private function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'size' => $product->getSize(),
            'stock' => $product->getStock(),
            'urlImage' => $product->getUrlImage(),
            'urlImageHover' => $product->getUrlImageHover(),
            'subCategories' => array_map(
                fn($sc) => ['id' => $sc->getId(), 'name' => $sc->getName()], 
                $product->getSubCategories()->toArray()
            ),
            'categories' => array_map(
                fn($c) => ['id' => $c->getId(), 'name' => $c->getName()],
                $product->getCategories()->toArray()
            ),
            'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
