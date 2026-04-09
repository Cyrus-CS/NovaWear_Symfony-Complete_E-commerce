<?php

namespace App\Form;

use App\Entity\Brand;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class BrandAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Brand::class,
            'choice_label' => 'name',
            'attr' => ['class' => 'form-control'],
            'label' => 'Brand',
            'placeholder' => 'Choose a Brand',
            // 'choice_label' => 'name',

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
