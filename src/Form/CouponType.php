<?php

namespace App\Form;

use App\Entity\Coupon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('code', TextType::class, [
                'attr' => ['maxlength' => 50, 'placeholder' => 'Code unique du coupon', 
                'help' => 'Le code que les clients utiliseront pour bénéficier de la réduction. Doit être unique.', 
                'label' => 'Code du coupon', 
                'required' => true,
                'class ' => 'form-control'
                ],
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['placeholder' => 'Description du coupon', 
                'help' => 'Une description facultative pour expliquer les détails du coupon.', 
                'label' => 'Description', 
                'required' => false,
                'class ' => 'form-control'
                ],
            ])
            ->add('discountType', TextType::class, [
                'attr' => ['placeholder' => 'Type de remise (percent ou fixed)', 
                'help' => 'Le type de remise : "percent" pour un pourcentage, "fixed" pour un montant fixe.', 
                'label' => 'Type de remise', 
                'required' => true,
                'class ' => 'form-control'
                ],
            ])
            ->add('discountValue', NumberType::class, [
                'attr' => ['placeholder' => 'Valeur de la remise', 
                'help' => 'La valeur de la remise (en pourcentage ou en montant fixe).', 
                'label' => 'Valeur de la remise', 
                'required' => true,
                'class ' => 'form-control'
                ],
            ])
            ->add('startsAt', null, [
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'Date de début de validité', 
                'help' => 'La date à partir de laquelle le coupon devient valide.', 
                'label' => 'Date de début', 
                'required' => false,
                'class ' => 'form-control'
                ],
            ])
            ->add('expiresAt', null, [
                'widget' => 'single_text',
                'attr' => ['placeholder' => 'Date d\'expiration', 
                'help' => 'La date à laquelle le coupon expire.', 
                'label' => 'Date d\'expiration', 
                'required' => false,
                'class ' => 'form-control'
                ],
            ])
            ->add('isActive', null, [
                'attr' => ['placeholder' => 'Actif', 
                'help' => 'Indique si le coupon est actif ou non.', 
                'label' => 'Actif', 
                'required' => false,
                'class ' => 'form-check-input'
                ],
            ])
            ->add('minCartTotal', NumberType::class, [
                'attr' => ['placeholder' => 'Montant minimum du panier', 
                'help' => 'Le montant minimum du panier pour que le coupon soit valide.', 
                'label' => 'Montant minimum du panier', 
                'required' => true,
                'class ' => 'form-control'
                ],
            ])
            ->add('products', ProductAutocompleteField::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Coupon::class,
        ]);
    }
}