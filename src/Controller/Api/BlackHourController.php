<?php

namespace App\Controller\Api;

use App\Repository\BlackHourRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/black-hour', name: 'api_black_hour_')]
class BlackHourController extends AbstractController
{
    public function __construct(
        private BlackHourRepository $blackHourRepository
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        // Récupérer toutes les promotions Black Hour actives
        $blackHourProducts = $this->blackHourRepository->findAllWithProductDetails();
        
        // Transformer les données en tableau associatif
        $data = [];
        foreach ($blackHourProducts as $promo) {
            $product = $promo->getProduct();
            
            // Sérialiser le produit
            $productData = $this->serializeProduct($product);
            
            // Ajouter les informations de promotion
            $productData['promotion'] = [
                'discountedPrice' => (float) $promo->getDiscountedPrice(),
                'originalPrice' => (float) $product->getPrice(),
                'discountPercentage' => $this->calculateDiscountPercentage(
                    (float) $product->getPrice(), 
                    (float) $promo->getDiscountedPrice()
                ),
                'startTime' => $promo->getStartTime() ? $promo->getStartTime()->format('Y-m-d\TH:i:sP') : null,
                'endTime' => $promo->getEndTime() ? $promo->getEndTime()->format('Y-m-d\TH:i:sP') : null,
                'timeRemaining' => $this->getTimeRemaining($promo->getEndTime()),
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

    private function calculateDiscountPercentage(float $originalPrice, float $discountedPrice): float
    {
        if ($originalPrice <= 0) {
            return 0;
        }
        
        $discount = (($originalPrice - $discountedPrice) / $originalPrice) * 100;
        return round($discount, 2);
    }
    
    private function getTimeRemaining(\DateTimeInterface $endTime): array
    {
        $now = new \DateTime();
        $interval = $now->diff($endTime);
        
        return [
            'days' => (int) $interval->format('%a'),
            'hours' => (int) $interval->format('%h'),
            'minutes' => (int) $interval->format('%i'),
            'seconds' => (int) $interval->format('%s'),
            'isActive' => $now < $endTime
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
