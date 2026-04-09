<?php

namespace App\Form;

use App\DTO\ContactDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name', TextType::class, [
                'required' => true,
                'label' => 'First Name',
                'attr' => [
                    'placeholder' => 'Enter your first name',
                    'class' => 'form-control',
                ],
            ])
            ->add('last_name', TextType::class, [
                'required' => true,
                'label' => 'Last Name',
                'attr' => [
                    'placeholder' => 'Enter your last name',
                    'class' => 'form-control',
                ],
            ])
            ->add('email', TextType::class, [
                'required' => true,
                'label' => 'Email Address',
                'attr' => [
                    'placeholder' => 'Enter your email address',
                    'class' => 'form-control',
                ],
            ])
            ->add('subject', TextType::class, [
                'required' => false,
                'label' => 'Subject',
                'attr' => [
                    'placeholder' => 'Enter the subject of your message',
                    'class' => 'form-control',
                ],
            ])
            ->add('message', TextareaType::class, [
                'required' => true,
                'label' => 'Message',
                'attr' => [
                    'placeholder' => 'Enter your message',
                    'class' => 'form-control',
                    'rows' => 5,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ContactDTO::class,
        ]);
    }
}
