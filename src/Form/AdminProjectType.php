<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AdminProjectType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('slug', TextType::class, [
                'label' => 'Slug',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('image', TextType::class, [
                'required' => false,
                'label' => 'Image',
                'attr' => ['class' => 'form-control', 'placeholder' => 'img/nom-image.jpg'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => ['class' => 'form-control', 'rows' => 6],
            ])
            ->add('dataJson', TextareaType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Données des actions par Pays (JSON)',
                'attr' => [
                    'class' => 'form-control font-monospace',
                    'rows' => 8,
                    'placeholder' => "[\n  {\"country\": \"Maroc\", \"count\": 40, \"price\": 500},\n  {\"country\": \"Sénégal\", \"count\": 20, \"price\": 300}\n]",
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Project::class]);
    }
}
