<?php

namespace App\Form;

use App\Entity\ProductTypeVariation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class ProductTypeVariationAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => ProductTypeVariation::class,
            'placeholder' => 'Choose a ProductTypeVariation',
            'attr' => [
                    'class' => 'form-control',
            ],
            'label' => 'Product Type Variation',
            'choice_label' => 'name',

            // choose which fields to use in the search
            // if not passed, *all* fields are used
            // 'searchable_fields' => ['name'],

            // 'security' => 'ROLE_SOMETHING',
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
