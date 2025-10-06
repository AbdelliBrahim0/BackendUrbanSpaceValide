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
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 16);
        $offset = ($page - 1) * $limit;

        $categoryId = $request->query->get('category');
        $subCategoryId = $request->query->get('subcategory');

        // Step 1: Create a query to get the paginated list of distinct product IDs
        $qb_ids = $repo->createQueryBuilder('p_ids')
            ->select('DISTINCT p_ids.id')
            ->orderBy('p_ids.name', 'ASC');

        // Apply filters to the ID query if they exist
        if ($categoryId || $subCategoryId) {
            if ($subCategoryId) {
                $qb_ids->innerJoin('p_ids.subCategories', 'sc_ids');
                $qb_ids->andWhere('sc_ids.id = :subCategoryId')->setParameter('subCategoryId', $subCategoryId);
            }
            if ($categoryId) {
                // This join is complex, it needs to check categories on product AND categories on subcategories
                $qb_ids->leftJoin('p_ids.subCategories', 'sc_for_cat_ids')
                       ->leftJoin('sc_for_cat_ids.categories', 'c1_ids')
                       ->leftJoin('p_ids.categories', 'c2_ids');
                $qb_ids->andWhere('c1_ids.id = :categoryId OR c2_ids.id = :categoryId')
                       ->setParameter('categoryId', $categoryId);
            }
        }

        $paginated_ids = $qb_ids->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getScalarResult();

        $productIds = array_column($paginated_ids, 'id');

        if (empty($productIds)) {
            return new JsonResponse(['success' => true, 'items' => []], Response::HTTP_OK);
        }

        // Step 2: Create the main query to fetch the products with their collections
        $qb = $repo->createQueryBuilder('p')
            ->where('p.id IN (:ids)')
            ->setParameter('ids', $productIds)
            ->leftJoin('p.subCategories', 'sc')
            ->leftJoin('p.categories', 'c')
            ->addSelect('sc', 'c')
            ->orderBy('p.name', 'ASC'); // Order again to maintain consistency

        $products = $qb->getQuery()->getResult();

        $data = [];
        foreach ($products as $product) {
            $data[] = $this->serializeProduct($product);
        }

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