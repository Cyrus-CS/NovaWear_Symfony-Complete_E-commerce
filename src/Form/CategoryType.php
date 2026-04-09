<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PreSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Vich\UploaderBundle\Form\Type\VichImageType;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Category name',
                'required' => true,
            ])
            ->add('slug', TextType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Slug (généré automatiquement à partir du nom si laissé vide)',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'attr' => ['class' => 'form-control'],
                'label' => 'Description',
                'required' => false,
            ])
            ->add('imageFile', VichImageType::class, [
                'label' => 'Image de la catégorie',
                'required' => false, // path contient déjà le nom du fichier, donc ce champ n'est pas obligatoire
                'attr' => [
                    'class' => 'form-control',
                    //Accepter uniquement les fichiers images de types JPEG, PNG, GIF, JPG, AVIF et WEBP
                    'accept' => 'image/jpeg, image/png, image/gif, image/jpg, image/webp, image/avif',
                ],
                'download_uri' => false, // Empêche le téléchargement de l'image
                'image_uri' => true, // Affiche l'image dans le formulaire
            ])
            ->add('isActive', null, [
                'attr' => ['class' => 'form-check-input'],
                'label' => 'Is Active',
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, [$this, 'slugAutoFill'])
        ;
    }
    public function slugAutoFill(PreSubmitEvent $event): void
    {
        $data = $event->getData();
        if (empty($data['slug']) && !empty($data['name'])) {
            $slugger = new AsciiSlugger();
            //regex slug
            $data['slug'] = $slugger->slug($data['name'])->lower();
            $event->setData($data);
        }
        
    }
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}