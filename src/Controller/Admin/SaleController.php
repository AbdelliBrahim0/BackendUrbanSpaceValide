<?php

namespace App\Controller\Admin;

use App\Entity\Sale;
use App\Entity\Product;
use App\Form\SaleType;
use App\Repository\SaleRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/sales')]
class SaleController extends AbstractController
{
    #[Route('/', name: 'app_admin_sale_index', methods: ['GET'])]
    public function index(SaleRepository $saleRepository): Response
    {
        return $this->render('admin/sale/index.html.twig', [
            'sales' => $saleRepository->findBy([], ['createdAt' => 'DESC']),
            'activeSales' => $saleRepository->findActiveSales(),
            'upcomingSales' => $saleRepository->findUpcomingSales(),
            'expiredSales' => $saleRepository->findExpiredSales(),
        ]);
    }

    #[Route('/new', name: 'app_admin_sale_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ProductRepository $productRepository): Response
    {
        $sale = new Sale();
        $products = $productRepository->findAll();

        if ($request->isMethod('POST')) {
            $productId = $request->request->get('product');
            $discountPercentage = $request->request->get('discount_percentage');
            $startDate = new \DateTime($request->request->get('start_date'));
            $endDate = new \DateTime($request->request->get('end_date'));
            $description = $request->request->get('description');

            if (!$productId || !$discountPercentage || !$startDate || !$endDate) {
                $this->addFlash('error', 'Veuillez remplir tous les champs obligatoires.');
                return $this->redirectToRoute('app_admin_sale_new');
            }

            $product = $productRepository->find($productId);
            if (!$product) {
                $this->addFlash('error', 'Produit non trouvé.');
                return $this->redirectToRoute('app_admin_sale_new');
            }

            $sale->setProduct($product);
            $sale->setDiscountPercentage($discountPercentage);
            $sale->setStartDate($startDate);
            $sale->setEndDate($endDate);
            $sale->setDescription($description);

            $entityManager->persist($sale);
            $entityManager->flush();

            $this->addFlash('success', 'La remise a été ajoutée avec succès.');
            return $this->redirectToRoute('app_admin_sale_index');
        }

        return $this->render('admin/sale/new.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_admin_sale_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Sale $sale, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            $discountPercentage = $request->request->get('discount_percentage');
            $startDate = new \DateTime($request->request->get('start_date'));
            $endDate = new \DateTime($request->request->get('end_date'));
            $description = $request->request->get('description');
            $isActive = $request->request->get('is_active') ? true : false;

            $sale->setDiscountPercentage($discountPercentage);
            $sale->setStartDate($startDate);
            $sale->setEndDate($endDate);
            $sale->setDescription($description);
            $sale->setIsActive($isActive);

            $entityManager->flush();

            $this->addFlash('success', 'La remise a été mise à jour avec succès.');
            return $this->redirectToRoute('app_admin_sale_index');
        }

        return $this->render('admin/sale/edit.html.twig', [
            'sale' => $sale,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_sale_delete', methods: ['POST'])]
    public function delete(Request $request, Sale $sale, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$sale->getId(), $request->request->get('_token'))) {
            $entityManager->remove($sale);
            $entityManager->flush();
            $this->addFlash('success', 'La remise a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_admin_sale_index');
    }

    #[Route('/{id}/toggle', name: 'app_admin_sale_toggle', methods: ['POST'])]
    public function toggle(Sale $sale, EntityManagerInterface $entityManager): Response
    {
        $sale->setIsActive(!$sale->getIsActive());
        $entityManager->flush();

        $status = $sale->getIsActive() ? 'activée' : 'désactivée';
        $this->addFlash('success', "La remise a été {$status} avec succès.");

        return $this->redirectToRoute('app_admin_sale_index');
    }
}