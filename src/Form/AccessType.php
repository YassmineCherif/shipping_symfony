<?php

namespace App\Form;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints as Assert;


class AccessType extends AbstractType
{

    public function testAccess(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Client' => 'client',
                    'Livreur' => 'livreur',
                    'Partenaire' => 'partenaire',
                ],
                'label' => 'Type',
                'placeholder' => 'Choisissez un type',
            ])
           
            ->add('submit', SubmitType::class, ['label' => 'Valider'])
            ->getForm();
    

    }

}