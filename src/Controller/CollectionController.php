<?php

namespace App\Controller;

use App\Entity\ProductCollection;
use App\Form\CollectionType;
use App\Repository\ProductCollectionRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/admin/collections')]
class CollectionController extends AbstractController
{
    private $entityManager;
    private $serializer;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
    }

    #[Route('', name: 'collection_index', methods: ['GET'])]
    public function index(ProductCollectionRepository $collectionRepository): JsonResponse
    {
        $collections = $collectionRepository->findAllWithProducts();
        
        return $this->json([
            'status' => 'success',
            'data' => $collections,
        ], 200, [], ['groups' => ['collection:read']]);
    }

    #[Route('/{id}', name: 'collection_show', methods: ['GET'])]
    public function show(ProductCollection $collection): JsonResponse
    {
        return $this->json([
            'status' => 'success',
            'data' => $collection,
        ], 200, [], ['groups' => ['collection:read']]);
    }

    #[Route('', name: 'collection_create', methods: ['POST'])]
    public function create(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $collection = new ProductCollection();
        
        $form = $this->createForm(CollectionType::class, $collection);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle products association
            if (isset($data['products']) && is_array($data['products'])) {
                $collection->getProducts()->clear();
                foreach ($data['products'] as $productId) {
                    $product = $productRepository->find($productId);
                    if ($product) {
                        $collection->addProduct($product);
                    }
                }
            }

            $this->entityManager->persist($collection);
            $this->entityManager->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'Collection created successfully',
                'data' => $collection,
            ], 201, [], ['groups' => ['collection:read']]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors,
        ], 400);
    }

    #[Route('/{id}', name: 'collection_update', methods: ['PUT'])]
    public function update(Request $request, ProductCollection $collection, ProductRepository $productRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $form = $this->createForm(CollectionType::class, $collection);
        $form->submit($data, false); // false to allow partial updates

        if ($form->isValid()) {
            // Handle products association
            if (isset($data['products']) && is_array($data['products'])) {
                $collection->getProducts()->clear();
                foreach ($data['products'] as $productId) {
                    $product = $productRepository->find($productId);
                    if ($product) {
                        $collection->addProduct($product);
                    }
                }
            }

            $this->entityManager->flush();

            return $this->json([
                'status' => 'success',
                'message' => 'Collection updated successfully',
                'data' => $collection,
            ], 200, [], ['groups' => ['collection:read']]);
        }

        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $this->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $errors,
        ], 400);
    }

    #[Route('/{id}', name: 'collection_delete', methods: ['DELETE'])]
    public function delete(ProductCollection $collection): JsonResponse
    {
        $this->entityManager->remove($collection);
        $this->entityManager->flush();

        return $this->json([
            'status' => 'success',
            'message' => 'Collection deleted successfully',
        ]);
    }
}
