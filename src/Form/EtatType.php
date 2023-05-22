<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EtatType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'Pas livré' => 'Pas livré',
                'Livré' => 'Livré',
                'En attente de livraison' => 'En attente de livraison',
            ],
            'label' => 'Etat colis'
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
