<?php

namespace App\Controller\Api;

use App\Repository\OrderRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[Route('/api/orders')]
class OrderController extends AbstractController
{
    #[Route('/user/{id}', name: 'api_orders_by_user', methods: ['GET'])]
    public function getUserOrders(
        int $id,
        OrderRepository $orderRepository,
        UserRepository $userRepository,
        Request $request
    ): JsonResponse {
        // Vérifier que l'utilisateur est authentifié
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        
        // Récupérer l'utilisateur demandé
        $user = $userRepository->find($id);
        
        if (!$user) {
            return $this->json([
                'status' => 'error',
                'message' => 'Utilisateur non trouvé'
            ], Response::HTTP_NOT_FOUND);
        }
        
        // Vérifier que l'utilisateur a le droit de voir ces commandes
        $currentUser = $this->getUser();
        if ($currentUser->getId() !== $user->getId() && !$this->isGranted('ROLE_ADMIN')) {
            return $this->json([
                'status' => 'error',
                'message' => 'Accès non autorisé'
            ], Response::HTTP_FORBIDDEN);
        }
        
        // Récupérer les commandes avec pagination
        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);
        $status = $request->query->get('status');
        
        $criteria = ['user' => $user];
        if ($status) {
            $criteria['status'] = $status;
        }
        
        $orders = $orderRepository->findBy(
            $criteria,
            ['createdAt' => 'DESC'],
            $limit,
            ($page - 1) * $limit
        );
        
        // Compter le nombre total de commandes pour la pagination
        $totalOrders = $orderRepository->count($criteria);
        
        // Formater les données de sortie
        $formattedOrders = [];
        foreach ($orders as $order) {
            $orderItems = [];
            foreach ($order->getItems() as $item) {
                $orderItems[] = [
                    'id' => $item->getId(),
                    'productName' => $item->getProduct()->getName(),
                    'quantity' => $item->getQuantity(),
                    'price' => $item->getPrice(),
                    'total' => $item->getPrice() * $item->getQuantity(),
                ];
            }
            
            $formattedOrders[] = [
                'id' => $order->getId(),
                'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
                'status' => $order->getStatus(),
                'total' => $order->getTotal(),
                'items' => $orderItems,
                'shippingAddress' => $order->getShippingAddress(),
            ];
        }
        
        return $this->json([
            'status' => 'success',
            'data' => [
                'orders' => $formattedOrders,
                'pagination' => [
                    'current_page' => $page,
                    'items_per_page' => $limit,
                    'total_items' => $totalOrders,
                    'total_pages' => ceil($totalOrders / $limit)
                ]
            ]
        ]);
    }
}
