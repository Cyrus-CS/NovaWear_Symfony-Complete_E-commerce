<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Color;
use App\Entity\Product;
use App\Entity\ProductTypeVariation;
use App\Entity\Size;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder

             // ➜ Champ multi-fichiers pour les images
            ->add('images', FileType::class, [
                'label' => 'Images',
                'mapped' => false,          // très important
                'required' => false,
                'multiple' => true,         // plusieurs fichiers d’un coup
                'attr' => [
                    'class' => 'form-control',
                    'accept' => 'image/jpeg, image/png, image/jpg, image/webp',
                ],
            ])

            // Image principale (un seul fichier)
            ->add('mainImage', FileType::class, [
                'label'    => 'Image principale',
                'mapped'   => false,
                'required' => false,
                'attr' => [
                    'class'  => 'form-control',
                    'accept' => 'image/jpeg, image/png, image/jpg, image/webp',
                ],
            ])
            
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Product name',
                'required' => true,
            ])
            ->add('slug', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Slug',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Description',
                'required' => true,
            ])
            ->add('shortDescription', TextareaType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Short Description',
                'required' => false,
            ])
            ->add('price', MoneyType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Price',
                'required' => true,
            ])
            ->add('compareAtPrice', MoneyType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Compare At Price',
                'required' => false,
            ])
            ->add('stock', IntegerType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Stock',
                'required' => true,
            ])
            ->add('brand', EntityType::class,[
                'class' => Brand::class,
                'choice_label' => 'name',
                'autocomplete' => true,
                'multiple' => false,
                'attr' => ['class' => 'form-control'],
                'label' => 'Brand of the product',
                'required' => true,
            ])
            ->add('isActive', null, [
                'attr' => ['class' => 'form-check-input'],
                'label' => 'Is Active',
                'required' => false,
            ])
            ->add('categories', EntityType::class, [
                'class' => Category::class,
                'choice_label' => 'name',
                'autocomplete' => true,
                'attr' => ['class' => 'form-control'],
                'label' => 'Categories',
                'multiple' => true,
                'required' => true,
            ])

            // Type de variation (ex : “T-shirt”, “Chaussures”, etc.)
            ->add('typeVariation', EntityType::class, [
                'class' => ProductTypeVariation::class,
                'choice_label' => 'name',
                'label' => 'Type de variation',
                'autocomplete' => true,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            // Couleurs disponibles pour ce produit
            ->add('colors', EntityType::class, [
                'class'        => Color::class,
                'choice_label' => 'name',
                'label'        => 'Couleurs',
                'multiple'     => true,
                'autocomplete' => true,
                'expanded'     => false,
                'required'     => false,
                'attr'         => ['class' => 'form-control'],
            ])

            // Tailles disponibles pour ce produit
            ->add('sizes', EntityType::class, [
                'class' => Size::class,
                'choice_label' => 'label',
                'label' => 'Tailles',
                'multiple' => true,
                'autocomplete' => true,
                'expanded' => false,
                'required' => false,
                'attr' => ['class' => 'form-control'],
            ])

            ->addEventListener(FormEvents::PRE_SUBMIT, $this->slugAutoFill(...))
        ;
    }

    public function slugAutoFill(PreSubmitEvent $event): void
    {
        $data = $event->getData();
        if (empty($data['slug']) && !empty($data['name'])) {
            $slugger = new AsciiSlugger();
            $data['slug'] = strtolower(($slugger->slug($data['name'])));
            $event->setData($data);
            }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}