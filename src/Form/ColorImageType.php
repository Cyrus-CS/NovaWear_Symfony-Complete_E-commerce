<?php
// src/Form/ColorImageType.php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\All;

class ColorImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Image principale de cette couleur — 1 seul fichier
            ->add('mainImage', FileType::class, [
                'label'    => false,
                'required' => false,
                'mapped'   => false, // non mappé, on gère manuellement dans le controller
                'constraints' => [
                    new File(
                        maxSize: '5M',
                        mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                        mimeTypesMessage: 'Format accepté : JPG, PNG, WEBP',
                    )
                ],
            ])
            // Images secondaires de cette couleur — multiple fichiers
            ->add('secondaryImages', FileType::class, [
                'label'    => 'Images secondaires de la vignettes (optionnel)',
                'required' => false,
                'mapped'   => false,
                'multiple' => true, // permet de sélectionner plusieurs fichiers
                'constraints' => [
                    new All([ // valide chaque fichier du tableau
                        new File(
                            maxSize: '5M',
                            mimeTypes: ['image/jpeg', 'image/png', 'image/webp'],
                            mimeTypesMessage: 'Format accepté : JPG, PNG, WEBP',
                        )
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
                // Pas de données liées, on gère manuellement dans le controller
                'color_id' => null,
                'csrf_protection' => true, 
        ]);
    }

    public function getBlockPrefix(): string
    {
        return 'color_image'; // sera surchargé dans le controller
    }
}