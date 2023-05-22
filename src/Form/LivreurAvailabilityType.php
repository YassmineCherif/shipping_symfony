<?php

namespace App\Form;

use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormEvent;
use App\Entity\LivreurAvailability;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GroupSequence;

class LivreurAvailabilityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Available' => true,
                    'Unavailable' => false,
                ],
                'expanded' => true,
                'multiple' => false,
            ])
            ->add('reason', TextareaType::class, [
                'required' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please provide a reason for unavailability',
                        'groups' => ['unavailable'],
                    ]),
                    new NotNull([
                        'message' => 'Please provide a reason for unavailability',
                        'groups' => ['unavailable'],
                    ]),
                ],
            ])
            ->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
                $data = $event->getData();
                $form = $event->getForm();
                if ($data['status'] === true) {
                    $form->remove('reason');
                } else {
                    $form->add('reason', TextareaType::class, [
                        'required' => true,
                        'constraints' => [
                            new NotBlank([
                                'message' => 'Please provide a reason for unavailability',
                                'groups' => ['unavailable'],
                            ]),
                            new NotNull([
                                'message' => 'Please provide a reason for unavailability',
                                'groups' => ['unavailable'],
                            ]),
                        ],
                    ]);
                }
            })
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => LivreurAvailability::class,
            'validation_groups' => function (FormInterface $form) {
                $data = $form->getData();
                if ($data->isStatus() === false) {
                    return ['Default', 'unavailable'];
                }
                return ['Default'];
            },
            'constraints_callback' => function (LivreurAvailability $availability) {
                if ($availability->isStatus() === true) {
                    return new GroupSequence(['Default']);
                }
                return null;
            },
        ]);
    }
    
}
