<?php

namespace App\Form;

use App\Entity\Reclamation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Length;
class ReclamationType extends AbstractType

{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('id_commande', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'L\'ID commande est obligatoire.']),
                    new Regex([
                        'pattern' => '/^\d{1,8}$/',
                        'message' => 'L\'ID commande doit contenir entre 1 et 8 chiffres.',
                    ]),
                ],
            ])
            ->add('id_produit', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'L\'ID produit est obligatoire.']),
                    new Regex([
                        'pattern' => '/^\d{1,8}$/',
                        'message' => 'L\'ID produit doit contenir entre 1 et 8 chiffres.',
                    ]),
                ],
            ])
            ->add('raison', TextType::class, [
                'constraints' => [
                    new NotBlank(['message' => 'La raison est obligatoire.']),
                ],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'La date est obligatoire.']),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
