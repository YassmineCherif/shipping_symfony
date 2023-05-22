<?php

namespace App\Form;

use App\Entity\MoyenDeTransport;
use App\Entity\Partenaire;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;


class MoyenDeTransportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('marque', null, [
            'constraints' => [
                new Assert\NotBlank(),
            ],
        ])

            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Camion' => '0',
                    'Voiture' => '1',
                ],
                'expanded' => true,
                'multiple' => false,
                'label' => 'Type de véhicule',
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Choice([
                        'choices' => ['0', '1'],
                    ]),
                ],
            ])
            ->add('matricule', null, [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Regex([
                        'pattern' => '/^\d/',
                        'message' => 'The matricule must start with a number',
                    ]),
                ],
            ])


        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MoyenDeTransport::class,
        ]);
    }
}
