<?php

namespace App\Form;

use App\Entity\ProductColorVariant;
use App\Entity\Size;
// use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\All;
use Symfony\Component\Validator\Constraints\File;

class ProductColorVariantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Prix spécifique (facultatif)
            ->add('price', MoneyType::class, [
                'label'    => 'Prix',
                'required' => false,
                'currency' => 'USD', // adapte à ta devise
            ])
            // Prix barré spécifique (facultatif)
            ->add('compareAtPrice', MoneyType::class, [
                'label'    => 'Prix barré',
                'required' => false,
                'currency' => 'USD',
            ])
            // Stock spécifique (facultatif)
            ->add('stock', IntegerType::class, [
                'label'    => 'Stock',
                'required' => false,
                'attr'     => ['min' => 0],
            ])

            // Tailles disponibles pour cette couleur (sous-ensemble)
            ->add('sizes', SizeAutocompleteField::class, [
            'class'        => Size::class,
            'choice_label' => 'label',
            'multiple'     => true,
            'required'     => false,
            'label'        => 'Tailles disponibles',
            'choices'      => $options['available_sizes'], // limite aux tailles pertinentes
            'attr'         => [
                'class' => 'form-select form-select-sm', // Bootstrap
            ],
        ])

            // Image principale de cette couleur — fichier unmapped
            ->add('mainImage', FileType::class, [
                'label'    => false,
                'required' => false,
                'mapped'   => false,
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Formats acceptés : JPG, PNG, WEBP',
                    )
                ],
            ])

            // Images secondaires de cette couleur — multiple fichiers unmapped
            ->add('secondaryImages', FileType::class, [
                'label'    => 'Images secondaires (optionnel)',
                'required' => false,
                'mapped'   => false,
                'multiple' => true,
                'constraints' => [
                    new All([
                        new File(
                            maxSize: '5M',
                            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                            mimeTypesMessage: 'Formats acceptés : JPG, PNG, WEBP',
                        )
                    ])
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'      => ProductColorVariant::class,
            'available_sizes' => [], // sera fourni par le contrôleur
        ]);

        $resolver->setAllowedTypes('available_sizes', 'array');
    }

    public function getBlockPrefix(): string
    {
        return 'color_variant';
    }
}