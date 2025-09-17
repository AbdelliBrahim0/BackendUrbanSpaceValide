<?php

namespace App\Controller\Admin;

use App\Entity\BlackFriday;
use App\Entity\Product;
use App\Form\BlackFridayType;
use App\Repository\BlackFridayRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/black-friday')]
class BlackFridayController extends AbstractController
{
    #[Route('/', name: 'app_admin_black_friday_index', methods: ['GET'])]
    public function index(
        Request $request,
        BlackFridayRepository $blackFridayRepository
    ): Response {
        $page = $request->query->getInt('page', 1);
        $limit = 10;
        
        $query = $blackFridayRepository->createQueryBuilder('bf')
            ->orderBy('bf.dateCreation', 'DESC')
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

        return $this->render('admin/blackfriday/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new', name: 'app_admin_black_friday_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        ProductRepository $produitRepository
    ): Response {
        $produits = $produitRepository->findAll();

        if ($request->isMethod('POST')) {
            $produitId = $request->request->get('produit');
            $nouveauPrix = $request->request->get('nouveau_prix');

            if (!$produitId || !$nouveauPrix) {
                $this->addFlash('error', 'Veuillez sélectionner un produit et spécifier un prix.');
                return $this->redirectToRoute('app_admin_black_friday_new');
            }

            $produit = $produitRepository->find($produitId);
            if (!$produit) {
                $this->addFlash('error', 'Produit non trouvé.');
                return $this->redirectToRoute('app_admin_black_friday_new');
            }

            // Vérifier si le produit est déjà en promotion
            $existingPromo = $entityManager->getRepository(BlackFriday::class)->findOneBy(['produit' => $produit]);
            
            if ($existingPromo) {
                $blackFriday = $existingPromo;
                $blackFriday->setNouveauPrix($nouveauPrix);
                $message = 'La promotion pour ce produit a été mise à jour.';
            } else {
                $blackFriday = new BlackFriday();
                $blackFriday->setProduit($produit);
                $blackFriday->setNouveauPrix($nouveauPrix);
                $message = 'Le produit a été ajouté à la promotion Black Friday avec succès.';
            }

            $entityManager->persist($blackFriday);
            $entityManager->flush();

            $this->addFlash('success', $message);
            return $this->redirectToRoute('app_admin_black_friday_index');
        }

        return $this->render('admin/blackfriday/new.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/{id}', name: 'app_admin_black_friday_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        BlackFriday $blackFriday,
        EntityManagerInterface $entityManager
    ): Response {
        if ($this->isCsrfTokenValid('delete' . $blackFriday->getId(), $request->request->get('_token'))) {
            $entityManager->remove($blackFriday);
            $entityManager->flush();
            $this->addFlash('success', 'La promotion a été supprimée avec succès.');
        }

        return $this->redirectToRoute('app_admin_black_friday_index');
    }
}
