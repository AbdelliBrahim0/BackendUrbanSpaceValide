<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin/products')]
#[IsGranted('ROLE_ADMIN')]
class ProductController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
    }

    #[Route('', name: 'api_admin_products_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $this->assertJson($request);
        $data = json_decode($request->getContent(), true);

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->submit($data);

        if (!$form->isValid()) {
            return new JsonResponse([
                'success' => false,
                'errors' => $this->collectFormErrors($form),
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($product);
        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'item' => $this->serializeProduct($product)
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'api_admin_products_update', methods: ['PUT', 'PATCH'])]
    public function update(Product $product, Request $request): JsonResponse
    {
        $this->assertJson($request);
        $data = json_decode($request->getContent(), true);

        $form = $this->createForm(ProductType::class, $product, ['method' => $request->getMethod()]);
        $form->submit($data, $request->getMethod() !== 'PATCH');

        if (!$form->isValid()) {
            return new JsonResponse([
                'success' => false,
                'errors' => $this->collectFormErrors($form),
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->em->flush();

        return new JsonResponse([
            'success' => true,
            'item' => $this->serializeProduct($product)
        ]);
    }

    #[Route('/{id}', name: 'api_admin_products_delete', methods: ['DELETE'])]
    public function delete(Product $product): JsonResponse
    {
        $this->em->remove($product);
        $this->em->flush();

        return new JsonResponse(['success' => true], Response::HTTP_NO_CONTENT);
    }

    private function assertJson(Request $request): void
    {
        if ($request->getContentTypeFormat() !== 'json') {
            throw new \RuntimeException('Expected JSON request');
        }
    }

    private function collectFormErrors(\Symfony\Component\Form\FormInterface $form): array
    {
        $errors = [];
        foreach ($form->getErrors(true) as $error) {
            $errors[$error->getOrigin()->getName()][] = $error->getMessage();
        }
        return $errors;
    }

    private function serializeProduct(Product $product): array
    {
        return [
            'id' => $product->getId(),
            'name' => $product->getName(),
            'description' => $product->getDescription(),
            'price' => $product->getPrice(),
            'size' => $product->getSize(),
            'stock' => $product->getStock(),
            'urlImage' => $product->getUrlImage(),
            'urlImageHover' => $product->getUrlImageHover(),
            'subCategories' => array_map(
                fn($sc) => ['id' => $sc->getId(), 'name' => $sc->getName()], 
                $product->getSubCategories()->toArray()
            ),
            'categories' => array_map(
                fn($c) => ['id' => $c->getId(), 'name' => $c->getName()],
                $product->getCategories()->toArray()
            ),
            'createdAt' => $product->getCreatedAt()->format('Y-m-d H:i:s'),
        ];
    }
}
