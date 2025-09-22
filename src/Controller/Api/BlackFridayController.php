<?php

namespace App\Controller\Api;

use App\Entity\BlackFriday;
use App\Repository\BlackFridayRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/black-friday', name: 'api_black_friday_')]
class BlackFridayController extends AbstractController
{
    public function __construct(
        private BlackFridayRepository $blackFridayRepository
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Récupérer toutes les promotions Black Friday actives
        $blackFridayProducts = $this->blackFridayRepository->findAllWithProductDetails();
        
        // Transformer les données en tableau associatif
        $data = [];
        foreach ($blackFridayProducts as $promo) {
            $product = $promo->getProduit();
            
            // Sérialiser le produit
            $productData = $this->serializeProduct($product);
            
            // Ajouter les informations de promotion
            $productData['promotion'] = [
                'newPrice' => (float) $promo->getNouveauPrix(),
                'originalPrice' => (float) $product->getPrice(),
                'discountPercentage' => $this->calculateDiscountPercentage(
                    (float) $product->getPrice(), 
                    (float) $promo->getNouveauPrix()
                ),
                'startDate' => $promo->getDateCreation() ? $promo->getDateCreation()->format('Y-m-d\TH:i:sP') : null,
            ];
            
            $data[] = $productData;
        }

        return $this->json([
            'success' => true,
            'data' => $data,
            'count' => count($data),
        ]);
    }

    private function calculateDiscountPercentage(float $originalPrice, float $newPrice): float
    {
        if ($originalPrice <= 0) {
            return 0;
        }
        
        $discount = (($originalPrice - $newPrice) / $originalPrice) * 100;
        return round($discount, 2);
    }
    
    private function serializeProduct($product): array
    {
        // Récupérer les catégories
        $categories = [];
        foreach ($product->getCategories() as $category) {
            $categories[] = [
                'id' => $category->getId(),
                'name' => $category->getName(),
            ];
        }
        
        // Récupérer les sous-catégories
        $subCategories = [];
        foreach ($product->getSubCategories() as $subCategory) {
            $subCategories[] = [
                'id' => $subCategory->getId(),
                'name' => $subCategory->getName(),
            ];
        }
        
        // Récupérer les collections de produits
        $collections = [];
        if (method_exists($product, 'getProductCollections')) {
            foreach ($product->getProductCollections() as $collection) {
                $collections[] = [
                    'id' => $collection->getId(),
                    'name' => $collection->getName(),
                ];
            }
        }
        
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => (float) $product->getPrice(),
            'size' => $product->getSize(),
            'stock' => (int) $product->getStock(),
            'urlImage' => $product->getUrlImage(),
            'urlImageHover' => $product->getUrlImageHover(),
            'createdAt' => $product->getCreatedAt() ? $product->getCreatedAt()->format('Y-m-d\TH:i:sP') : null,
            'categories' => $categories,
            'subCategories' => $subCategories,
            'collections' => $collections,
        ];
    }
}
