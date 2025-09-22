<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Product;
use App\Entity\User;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ProductRepository $productRepository;
    private SerializerInterface $serializer;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        SerializerInterface $serializer
    ) {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'order_create', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non connecté'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        // Validation des données
        if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
            return $this->json([
                'success' => false,
                'message' => 'Le panier est vide'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Création de la commande
        $order = new Order();
        $order->setUser($user);
        $order->setStatus('pending');
        $order->setShippingAddress($user->getAdresse());
        $order->setPhoneNumber($user->getTelephone());

        // Ajout des articles à la commande
        foreach ($data['items'] as $itemData) {
            if (!isset($itemData['product_id']) || !isset($itemData['quantity'])) {
                continue;
            }

            $product = $this->productRepository->find($itemData['product_id']);
            if (!$product) {
                continue;
            }

            $orderItem = new OrderItem();
            $orderItem->setProduct($product);
            $orderItem->setQuantity($itemData['quantity']);
            $orderItem->setPrice($product->getPrice());
            $orderItem->setSource($itemData['source'] ?? null);
            
            $order->addItem($orderItem);
        }

        // Vérification qu'il y a au moins un article valide
        if ($order->getItems()->count() === 0) {
            return $this->json([
                'success' => false,
                'message' => 'Aucun article valide dans la commande'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Calcul du total
        $order->calculateTotal();

        // Enregistrement en base de données
        $this->entityManager->persist($order);
        $this->entityManager->flush();

        // Préparation de la réponse
        $orderData = $this->serializeOrder($order);

        return $this->json([
            'success' => true,
            'message' => 'Commande créée avec succès',
            'order' => $orderData
        ], Response::HTTP_CREATED);
    }

    #[Route('', name: 'order_list', methods: ['GET'])]
    public function listOrders(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non connecté'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $orders = $user->getOrders();
        $ordersData = [];

        foreach ($orders as $order) {
            $ordersData[] = $this->serializeOrder($order);
        }

        return $this->json([
            'success' => true,
            'orders' => $ordersData
        ]);
    }

    #[Route('/{id}', name: 'order_show', methods: ['GET'])]
    public function showOrder(int $id): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();
        
        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non connecté'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $order = $this->entityManager->getRepository(Order::class)->findOneBy([
            'id' => $id,
            'user' => $user
        ]);

        if (!$order) {
            return $this->json([
                'success' => false,
                'message' => 'Commande non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        $orderData = $this->serializeOrder($order);

        return $this->json([
            'success' => true,
            'order' => $orderData
        ]);
    }

    private function serializeOrder(Order $order): array
    {
        $items = [];
        
        foreach ($order->getItems() as $item) {
            $product = $item->getProduct();
            $items[] = [
                'id' => $item->getId(),
                'product_id' => $product->getId(),
                'product_name' => $product->getName(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
                'source' => $item->getSource(),
                'total' => $item->getTotal()
            ];
        }

        return [
            'id' => $order->getId(),
            'created_at' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'status' => $order->getStatus(),
            'total' => $order->getTotal(),
            'shipping_address' => $order->getShippingAddress(),
            'phone_number' => $order->getPhoneNumber(),
            'items' => $items
        ];
    }
}
