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
                'constraints' => [
                    new NotBlank(['message' => 'Le montant est obligatoire.']),
                    new Regex([
                        'pattern' => '/^\d{1,8}$/',
                        'message' => 'Le montant doit contenir entre 1 et 8 chiffres.',
                    ]),
                ],
            ])
            ->add('mode_remboursement', ChoiceType::class, [
                'choices' => [
                    'Virement' => 'virement',
                    'Chèque' => 'cheque',
                    'Espèces' => 'especes',
                ],
                'constraints' => [
                    new NotBlank(['message' => 'Le mode de remboursement est obligatoire.']),
                ],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date est obligatoire.']),
                ],
            ])
            ->add('id_reclamation', EntityType::class, [
                'class' => Reclamation::class,
                'choice_label' => 'id',
                'constraints' => [
                    new NotBlank(['message' => 'La réclamation associée est obligatoire.']),
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
