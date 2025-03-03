<?php

namespace App\Form;

use App\Entity\Reclamation;
use App\Entity\Remboursement;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;

class RemboursementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('montant', TextType::class, [
            'label' => 'sum', // This is the correct place for the label
            'constraints' => [
                new NotBlank(['message' => 'Amount is required.']),
                new Regex([
                    'pattern' => '/^\d{1,8}$/',
                    'message' => 'Amount must contain between 1 and 8 digits.',
                ]),
            ],
        ])
        
            ->add('mode_remboursement', ChoiceType::class, [
                'label' => 'Refund mode', 
                'choices' => [
                    'Bank Transfer' => 'virement',
                    'Cheque' => 'cheque',
                    'Cash' => 'especes',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Refund method is required.']),
                ],
            ])
            
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Date is required.']),
                ],
            ])
            ->add('id_reclamation', EntityType::class, [
                'class' => Reclamation::class,
                'choice_label' => 'id',
                'constraints' => [
                    new NotBlank(['message' => 'Associated claim is required.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Remboursement::class,
        ]);
    }
}
