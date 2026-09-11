<?php

namespace App\Form;

use App\Entity\Donation;
use App\Repository\ProjectRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class DonationType extends AbstractType
{
    public function __construct(private ProjectRepository $projectRepository)
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Genere les choix a partir des projets existants (title => slug)
        $projects = $this->projectRepository->findAll();
        $choices = [];
        foreach ($projects as $project) {
            $choices[$project->getTitle()] = $project->getSlug();
        }

        $builder
            ->add('type', ChoiceType::class, [
                'choices' => $choices,
                'expanded' => true,
                'multiple' => false,
                'label' => 'Type de don',
                'choice_attr' => function($choice, $key, $value) {
                    return ['class' => 'form-check-input'];
                }
            ])

            ->add('amount', MoneyType::class, [
                'currency' => false,
                'scale' => 0,
                'label' => 'Montant du don',
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: 50'
                ],
            ])

            ->add('firstName', TextType::class, ['label' => 'Prénom'])
            ->add('lastName', TextType::class, ['label' => 'Nom'])
            ->add('email', EmailType::class, ['label' => 'Email'])
            ->add('address', TextType::class, ['label' => 'Adresse'])
            ->add('city', TextType::class, ['label' => 'Ville'])
            ->add('zip', TextType::class, ['label' => 'Code postal'])
            ->add('country', \Symfony\Component\Form\Extension\Core\Type\CountryType::class, [
                'label' => 'Pays',
                'preferred_choices' => ['FR'],
                'attr' => ['class' => 'form-select']
            ]);

            if ($options['show_save_info']) {
                $builder->add('saveInfo', CheckboxType::class, [
                    'label' => 'Sauvegarder mes informations pour la prochaine fois',
                    'mapped' => false,
                    'required' => false,
                ]);
            }

            $builder->add('submit', SubmitType::class, [
                'label' => 'Faire un don',
                'attr' => ['class' => 'btn btn-success mt-3 btn-lg'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Donation::class,
            'show_save_info' => false,
        ]);
    }
}