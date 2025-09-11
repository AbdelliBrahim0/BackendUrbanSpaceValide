<?php

namespace App\Controller;

use App\Repository\ProductCollectionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/collections')]
class PublicCollectionController extends AbstractController
{
    #[Route('', name: 'public_collection_index', methods: ['GET'])]
    public function index(ProductCollectionRepository $collectionRepository): JsonResponse
    {
        $collections = $collectionRepository->findAllWithProducts();
        
        return $this->json([
            'status' => 'success',
            'data' => $collections,
        ], 200, [], ['groups' => ['collection:read', 'product:read']]);
    }

    #[Route('/{id}', name: 'public_collection_show', methods: ['GET'])]
    public function show(int $id, ProductCollectionRepository $collectionRepository): JsonResponse
    {
        $collection = $collectionRepository->findWithProducts($id);
        
        if (!$collection) {
            return $this->json([
                'status' => 'error',
                'message' => 'Collection not found',
            ], 404);
        }
        
        return $this->json([
            'status' => 'success',
            'data' => $collection,
        ], 200, [], ['groups' => ['collection:read', 'product:read']]);
    }
}
