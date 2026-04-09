<?php

namespace App\Form;

use App\Entity\ProductTypeVariation;
use App\Entity\Size;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SizeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('label', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'Label',
            ])
            ->add('slug', TextType::class, [
                'attr' => [
                    'class' => 'form-control',
                ],
                'label' => 'Slug',
            ])
            ->add('position', IntegerType::class, [
                'attr' => [
                    'min' => 1,
                    'max' => 7,
                    'class' => 'form-control',
                ],
            ])
            ->add('productTypeVariation', EntityType::class, [
                'class' => ProductTypeVariation::class,
                'choice_label' => 'name',
                'autocomplete' => true,
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
                
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Size::class,
        ]);
    }
}
