<?php

namespace App\Controller;

use App\Entity\SubCategory;
use App\Form\SubCategoryType;
use App\Repository\SubCategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/subcategories')]
#[IsGranted('ROLE_ADMIN')]
class SubCategoryController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'api_admin_subcategories_list', methods: ['GET'])]
    public function list(SubCategoryRepository $repo): JsonResponse
    {
        $subCategories = $repo->createQueryBuilder('sc')
            ->leftJoin('sc.categories', 'c')
            ->leftJoin('sc.products', 'p')
            ->leftJoin('p.subCategories', 'p_sc')
            ->leftJoin('p_sc.categories', 'p_cat')
            ->addSelect('c', 'p', 'p_sc', 'p_cat')
            ->orderBy('sc.name', 'ASC')
            ->getQuery()
            ->getResult();

        $data = array_map(function (SubCategory $sc) {
            return [
                'id' => $sc->getId(),
                'name' => $sc->getName(),
                'categories' => array_map(fn($c) => [
                    'id' => $c->getId(), 
                    'name' => $c->getName()
                ], $sc->getCategories()->toArray()),
                'products' => array_map(function($p) {
                    return [
                        'id' => $p->getId(),
                        'name' => $p->getName(),
                        'description' => $p->getDescription(),
                        'price' => $p->getPrice(),
                        'size' => $p->getSize(),
                        'stock' => $p->getStock(),
                        'urlImage' => $p->getUrlImage(),
                        'urlImageHover' => $p->getUrlImageHover(),
                        'subCategories' => array_map(fn($sc) => [
                            'id' => $sc->getId(),
                            'name' => $sc->getName()
                        ], $p->getSubCategories()->toArray()),
                        'categories' => array_map(fn($c) => [
                            'id' => $c->getId(),
                            'name' => $c->getName(),
                        ], $p->getCategories()->toArray()),
                        'createdAt' => $p->getCreatedAt()->format('Y-m-d H:i:s')
                    ];
                }, $sc->getProducts()->toArray()),
                'createdAt' => $sc->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $subCategories);

        return new JsonResponse(['success' => true, 'items' => $data], Response::HTTP_OK);
    }

    #[Route('', name: 'api_admin_subcategories_create', methods: ['POST'])]
    public function create(Request $request, SubCategoryRepository $repo): JsonResponse
    {
        $this->assertJson($request);
        $data = json_decode($request->getContent(), true);

        $subCategory = new SubCategory();
        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->submit($data, false);

        if (!$form->isValid()) {
            return new JsonResponse([
                'success' => false,
                'errors' => $this->collectFormErrors($form),
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($subCategory);
        $this->em->flush();

        // Recharger la sous-catégorie avec toutes les relations
        $subCategory = $repo->createQueryBuilder('sc')
            ->leftJoin('sc.categories', 'c')
            ->leftJoin('sc.products', 'p')
            ->leftJoin('p.subCategories', 'p_sc')
            ->leftJoin('p_sc.categories', 'p_cat')
            ->addSelect('c', 'p', 'p_sc', 'p_cat')
            ->where('sc.id = :id')
            ->setParameter('id', $subCategory->getId())
            ->getQuery()
            ->getOneOrNullResult();

        return new JsonResponse([
            'success' => true,
            'item' => [
                'id' => $subCategory->getId(),
                'name' => $subCategory->getName(),
                'categories' => array_map(fn($c) => [
                    'id' => $c->getId(), 
                    'name' => $c->getName()
                ], $subCategory->getCategories()->toArray()),
                'products' => array_map(function($p) {
                    return [
                        'id' => $p->getId(),
                        'name' => $p->getName(),
                        'description' => $p->getDescription(),
                        'price' => $p->getPrice(),
                        'size' => $p->getSize(),
                        'stock' => $p->getStock(),
                        'urlImage' => $p->getUrlImage(),
                        'urlImageHover' => $p->getUrlImageHover(),
                        'subCategories' => array_map(fn($sc) => [
                            'id' => $sc->getId(),
                            'name' => $sc->getName(),
                            'category' => $sc->getCategories()->first() ? [
                                'id' => $sc->getCategories()->first()->getId(),
                                'name' => $sc->getCategories()->first()->getName()
                            ] : null
                        ], $p->getSubCategories()->toArray()),
                        'categories' => array_map(fn($c) => [
                            'id' => $c->getId(),
                            'name' => $c->getName(),
                        ], $p->getCategories()->toArray()),
                        'createdAt' => $p->getCreatedAt()->format('Y-m-d H:i:s')
                    ];
                }, $subCategory->getProducts()->toArray()),
                'createdAt' => $subCategory->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_admin_subcategories_get', methods: ['GET'])]
    public function getOne(SubCategory $subCategory, SubCategoryRepository $repo): JsonResponse
    {
        // Recharger la sous-catégorie avec toutes les relations nécessaires
        $subCategory = $repo->createQueryBuilder('sc')
            ->leftJoin('sc.categories', 'c')
            ->leftJoin('sc.products', 'p')
            ->leftJoin('p.subCategories', 'p_sc')
            ->leftJoin('p_sc.categories', 'p_cat')
            ->addSelect('c', 'p', 'p_sc', 'p_cat')
            ->where('sc.id = :id')
            ->setParameter('id', $subCategory->getId())
            ->getQuery()
            ->getOneOrNullResult();

        if (!$subCategory) {
            return new JsonResponse([
                'success' => false,
                'error' => 'Sous-catégorie non trouvée'
            ], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'success' => true,
            'item' => [
                'id' => $subCategory->getId(),
                'name' => $subCategory->getName(),
                'categories' => array_map(fn($c) => [
                    'id' => $c->getId(), 
                    'name' => $c->getName()
                ], $subCategory->getCategories()->toArray()),
                'products' => array_map(function($p) {
                    return [
                        'id' => $p->getId(),
                        'name' => $p->getName(),
                        'description' => $p->getDescription(),
                        'price' => $p->getPrice(),
                        'size' => $p->getSize(),
                        'stock' => $p->getStock(),
                        'urlImage' => $p->getUrlImage(),
                        'urlImageHover' => $p->getUrlImageHover(),
                        'subCategories' => array_map(fn($sc) => [
                            'id' => $sc->getId(),
                            'name' => $sc->getName(),
                            'category' => $sc->getCategories()->first() ? [
                                'id' => $sc->getCategories()->first()->getId(),
                                'name' => $sc->getCategories()->first()->getName()
                            ] : null
                        ], $p->getSubCategories()->toArray()),
                        'categories' => array_map(fn($c) => [
                            'id' => $c->getId(),
                            'name' => $c->getName(),
                        ], $p->getCategories()->toArray()),
                        'createdAt' => $p->getCreatedAt()->format('Y-m-d H:i:s')
                    ];
                }, $subCategory->getProducts()->toArray()),
                'createdAt' => $subCategory->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_admin_subcategories_update', methods: ['PUT', 'PATCH'])]
    public function update(SubCategory $subCategory, Request $request, SubCategoryRepository $repo): JsonResponse
    {
        $this->assertJson($request);
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(SubCategoryType::class, $subCategory);
        $form->submit($data, $request->getMethod() !== 'PATCH');

        if (!$form->isValid()) {
            return new JsonResponse([
                'success' => false,
                'errors' => $this->collectFormErrors($form),
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        // Recharger la sous-catégorie avec toutes les relations
        $subCategory = $repo->createQueryBuilder('sc')
            ->leftJoin('sc.categories', 'c')
            ->leftJoin('sc.products', 'p')
            ->leftJoin('p.subCategories', 'p_sc')
            ->leftJoin('p_sc.categories', 'p_cat')
            ->addSelect('c', 'p', 'p_sc', 'p_cat')
            ->where('sc.id = :id')
            ->setParameter('id', $subCategory->getId())
            ->getQuery()
            ->getOneOrNullResult();

        return new JsonResponse([
            'success' => true,
            'item' => [
                'id' => $subCategory->getId(),
                'name' => $subCategory->getName(),
                'categories' => array_map(fn($c) => [
                    'id' => $c->getId(), 
                    'name' => $c->getName()
                ], $subCategory->getCategories()->toArray()),
                'products' => array_map(function($p) {
                    return [
                        'id' => $p->getId(),
                        'name' => $p->getName(),
                        'description' => $p->getDescription(),
                        'price' => $p->getPrice(),
                        'size' => $p->getSize(),
                        'stock' => $p->getStock(),
                        'urlImage' => $p->getUrlImage(),
                        'urlImageHover' => $p->getUrlImageHover(),
                        'subCategories' => array_map(fn($sc) => [
                            'id' => $sc->getId(),
                            'name' => $sc->getName(),
                            'category' => $sc->getCategories()->first() ? [
                                'id' => $sc->getCategories()->first()->getId(),
                                'name' => $sc->getCategories()->first()->getName()
                            ] : null
                        ], $p->getSubCategories()->toArray()),
                        'createdAt' => $p->getCreatedAt()->format('Y-m-d H:i:s')
                    ];
                }, $subCategory->getProducts()->toArray()),
                'createdAt' => $subCategory->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ], Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'api_admin_subcategories_delete', methods: ['DELETE'])]
    public function delete(SubCategory $subCategory): JsonResponse
    {
        $this->em->remove($subCategory);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Sous-catégorie supprimée avec succès.'
        ], Response::HTTP_OK);
    }

    private function assertJson(Request $request): void
    {
        $contentType = $request->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'application/json')) {
            throw $this->createNotFoundException('Content-Type doit être application/json');
        }
        $raw = $request->getContent();
        if ($raw !== '' && json_decode($raw, true) === null && json_last_error() !== JSON_ERROR_NONE) {
            throw $this->createNotFoundException('JSON invalide');
        }
    }

    private function collectFormErrors(\Symfony\Component\Form\FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }
        return $errors;
    }

    private function serializeSubCategory(SubCategory $subCategory): array
    {
        return [
            'id' => $subCategory->getId(),
            'name' => $subCategory->getName(),
            'categories' => array_map(
                fn($c) => ['id' => $c->getId(), 'name' => $c->getName()], 
                $subCategory->getCategories()->toArray()
            ),
            'products' => array_map(
                fn($p) => ['id' => $p->getId(), 'name' => $p->getName()], 
                $subCategory->getProducts()->toArray()
            ),
            'createdAt' => $subCategory->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
