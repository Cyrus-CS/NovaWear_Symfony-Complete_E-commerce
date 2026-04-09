<?php

namespace App\Form;

use App\Entity\ProductImage;
use Symfony\Component\Form\AbstractType;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
class ProductImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image',
                'required' => false, // path contient déjà le nom du fichier, donc ce champ n'est pas obligatoire
                'attr' => [
                    'class' => 'form-control',
                    //Accepter uniquement les fichiers images de types JPEG, PNG, GIF, JPG, et WEBP
                    'accept' => 'image/jpeg, image/png, image/gif, image/jpg, image/webp',
                ],
                'allow_delete' => false, // Permet de supprimer l'image
                'download_uri' => false, // Empêche le téléchargement de l'image
                'image_uri' => true, // Affiche l'image dans le formulaire
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProductImage::class,
        ]);
    }
}
