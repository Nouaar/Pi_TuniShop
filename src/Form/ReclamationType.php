<?php

namespace App\Form;

use App\Entity\Reclamation;
use App\Entity\Checkout;
use App\Entity\Products;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Vich\UploaderBundle\Form\Type\VichImageType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TelType;

class ReclamationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Use EntityType for id_commande to map to Checkout entity
            ->add('id_commande', EntityType::class, [
                'class' => Checkout::class,
                'choice_label' => 'id', 
                'label' => 'Order ID',
                'constraints' => [
                    new NotBlank(['message' => 'Order ID is required.']),
                ],
            ])
            ->add('id_produit', EntityType::class, [
                'class' => Products::class,
                'choice_label' => 'id', 
                'label' => 'Product ID',
                'constraints' => [
                    new NotBlank(['message' => 'Product ID is required.']),
                    new Regex([
                        'pattern' => '/^\d{1,8}$/',
                        'message' => 'Product ID must contain between 1 and 8 digits.',
                    ]),
                ],
            ])
            ->add('raison', TextType::class, [
                'label' => 'Reason for the claim',
                'constraints' => [
                    new NotBlank(['message' => 'Reason for the claim is required.']),
                ],
            ])
            ->add('date', DateType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new NotBlank(['message' => 'Date is required.']),
                ],
            ])
            ->add('photoFile', HiddenType::class, [
                'mapped' => false, // If the field is not directly related to the entity
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Reclamation::class,
        ]);
    }
}
