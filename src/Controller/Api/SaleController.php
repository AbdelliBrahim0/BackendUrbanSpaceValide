<?php

namespace App\Controller\Api;

use App\Repository\SaleRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/sales', name: 'api_sale_')]
class SaleController extends AbstractController
{
    public function __construct(
        private SaleRepository $saleRepository
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Récupérer toutes les soldes actives
        $saleProducts = $this->saleRepository->findAllWithProductDetails();
        
        // Transformer les données en tableau associatif
        $data = [];
        foreach ($saleProducts as $sale) {
            $product = $sale->getProduct();
            
            // Sérialiser le produit
            $productData = $this->serializeProduct($product);
            
            // Calculer le nouveau prix
            $originalPrice = (float) $product->getPrice();
            $discountPercentage = (float) $sale->getDiscountPercentage();
            $discountedPrice = $originalPrice * (1 - ($discountPercentage / 100));
            
            // Ajouter les informations de promotion
            $productData['sale'] = [
                'discountPercentage' => $discountPercentage,
                'originalPrice' => $originalPrice,
                'discountedPrice' => round($discountedPrice, 2),
                'startDate' => $sale->getStartDate() ? $sale->getStartDate()->format('Y-m-d\TH:i:sP') : null,
                'endDate' => $sale->getEndDate() ? $sale->getEndDate()->format('Y-m-d\TH:i:sP') : null,
                'timeRemaining' => $this->getTimeRemaining($sale->getEndDate()),
                'description' => $sale->getDescription(),
            ];
            
            $data[] = $productData;
        }

        return $this->json([
            'success' => true,
            'data' => $data,
            'count' => count($data),
            'currentTime' => (new \DateTime())->format('Y-m-d\TH:i:sP')
        ]);
    }
    
    private function getTimeRemaining(\DateTimeInterface $endDate): array
    {
        $now = new \DateTime();
        $interval = $now->diff($endDate);
        
        return [
            'days' => (int) $interval->format('%a'),
            'hours' => (int) $interval->format('%h'),
            'minutes' => (int) $interval->format('%i'),
            'seconds' => (int) $interval->format('%s'),
            'isActive' => $now < $endDate
        ];
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
