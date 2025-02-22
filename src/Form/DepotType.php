<?php
namespace App\Form;

use App\Entity\Depot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class DepotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('adresse')
            ->add('stock_capacity')
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'Actif' => 'Actif',
                    'Inactif' => 'Inactif',
                    'Maintenance' => 'Maintenance',
                ],
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Submit',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Depot::class,
        ]);
    }
}
