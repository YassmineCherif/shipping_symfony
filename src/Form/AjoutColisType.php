<?php

namespace App\Form;

use App\Entity\Colis;
use App\Entity\Partenaire;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AjoutColisType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('hauteur', NumberType::class, [
                'label' => 'Hauteur',
                'scale' => 2, // nombre de décimales autorisées
                'attr' => [
                    'min' => 0, // valeur minimale
                    'max' => 100, // valeur maximale
                ],
            ])
            ->add('largeur', NumberType::class, [
                'label' => 'Largeur',
                'scale' => 2, // nombre de décimales autorisées
                'attr' => [
                    'min' => 0, // valeur minimale
                    'max' => 100, // valeur maximale
                ],
            ])
            ->add('poids', NumberType::class, [
                'label' => 'Poids',
                'scale' => 2, // nombre de décimales autorisées
                'attr' => [
                    'min' => 0, // valeur minimale
                    'max' => 100, // valeur maximale
                ],
            ])
            ->add('fragile')
            ->add('inflammable')
            ->add('depart', TextType::class, [
                'label' => "De"
            ])
            ->add('destination', TextType::class, [
                'label' => "A"
            ])
            ->add('zone', ChoiceType::class, [
                'label' => 'Zone',
                'choices'  => [
                    'Nationale' => 'Nationale',
                    'Internationale' => 'Internationale',
                ],  
            ])
            ->add('urgent')
            ->add('id_partenaire', EntityType::class, [
                'label' => 'Choisissez un partenaire',
                'class' => Partenaire :: class,
                'choice_label' => 'nom',
            ])

            ->add('submit', SubmitType::class, [
                'label' => "Envoyer"
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Colis::class,
        ]);
    }
}
