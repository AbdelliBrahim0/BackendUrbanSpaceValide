<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductCollection;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CollectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom de la collection'
                ],
                'required' => true,
            ])
            ->add('description1', TextareaType::class, [
                'label' => 'Description 1',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Première description de la collection'
                ],
                'required' => false,
            ])
            ->add('description2', TextareaType::class, [
                'label' => 'Description 2',
                'attr' => [
                    'class' => 'form-control',
                    'rows' => 4,
                    'placeholder' => 'Deuxième description de la collection'
                ],
                'required' => false,
            ])
            ->add('url1', UrlType::class, [
                'label' => 'URL 1',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://example.com'
                ],
                'required' => false,
                'default_protocol' => 'https',
            ])
            ->add('url2', UrlType::class, [
                'label' => 'URL 2',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://example.com'
                ],
                'required' => false,
                'default_protocol' => 'https',
            ])
            ->add('url3', UrlType::class, [
                'label' => 'URL 3',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'https://example.com'
                ],
                'required' => false,
                'default_protocol' => 'https',
            ])
            ->add('products', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => false,
                'by_reference' => false,
                'attr' => [
                    'class' => 'form-select',
                    'data-choices' => 'true',
                ],
                'label' => 'Produits',
                'required' => false,
                'placeholder' => 'Sélectionnez des produits',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductCollection::class,
        ]);
    }
}
