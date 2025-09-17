<?php

namespace App\Controller\Admin;

use App\Entity\BlackHour;
use App\Entity\Product;
use App\Repository\BlackHourRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\Tools\Pagination\Paginator;

#[Route('/admin/black-hour')]
class BlackHourController extends AbstractController
{
    #[Route('/', name: 'app_admin_black_hour_index', methods: ['GET'])]
    public function index(
        Request $request,
        BlackHourRepository $blackHourRepository
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        
        $query = $blackHourRepository->createQueryBuilder('bh')
            ->orderBy('bh.createdAt', 'DESC')
            ->getQuery();
            
        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $pagesCount = ceil($totalItems / $limit);
        
        $query->setFirstResult(($page - 1) * $limit)
              ->setMaxResults($limit);
              
        $pagination = [
            'currentPage' => $page,
            'totalItems' => $totalItems,
            'itemsPerPage' => $limit,
            'pagesCount' => $pagesCount,
            'items' => $query->getResult()
        ];

        return $this->render('admin/blackhour/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_admin_black_hour_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository
    ): Response {
        $products = $productRepository->findAll();

        if ($request->isMethod('POST')) {
            $productId = $request->request->get('product');
            $discountedPrice = $request->request->get('discounted_price');
            $startTime = new \DateTime($request->request->get('start_time'));
            $endTime = new \DateTime($request->request->get('end_time'));

            if (!$productId || !$discountedPrice || !$startTime || !$endTime) {
                $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
                return $this->redirectToRoute('app_admin_black_hour_new');
            }

            $product = $productRepository->find($productId);
            if (!$product) {
                $this->addFlash('error', 'Produit non trouvé.');
                return $this->redirectToRoute('app_admin_black_hour_new');
            }

            $blackHour = new BlackHour();
            $blackHour->setProduct($product);
            $blackHour->setDiscountedPrice($discountedPrice);
            $blackHour->setStartTime($startTime);
            $blackHour->setEndTime($endTime);

            $entityManager->persist($blackHour);
            $entityManager->flush();

            $this->addFlash('success', 'La promotion Black Hour a été ajoutée avec succès.');
            return $this->redirectToRoute('app_admin_black_hour_index');
        }

        return $this->render('admin/blackhour/new.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_black_hour_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        BlackHour $blackHour,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete'.$blackHour->getId(), $request->request->get('_token'))) {
            $entityManager->remove($blackHour);
            $entityManager->flush();
            $this->addFlash('success', 'La promotion Black Hour a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_admin_black_hour_index');
    }
}
