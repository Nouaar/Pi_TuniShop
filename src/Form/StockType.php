<?php
namespace App\Form;

use App\Entity\Depot;
use App\Entity\Products;
use App\Entity\Stock;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('quantite_min', IntegerType::class, [
                'label' => 'Quantité Minimum',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('quantite_max', IntegerType::class, [
                'label' => 'Quantité Maximum',
                'attr' => ['class' => 'form-control'],
            ])
            ->add('status', ChoiceType::class, [
                'choices' => [
                    'disponible' => 'disponible',
                    'indisponible' => 'indisponible',
                ],
                'label' => 'Statut',
                'attr' => ['class' => 'form-select'],
            ])
            ->add('Product', EntityType::class, [
                'class' => Products::class,
                'choice_label' => 'title',
            ])
            
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => ['class' => 'btn btn-success w-100']
            ]);
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
        ]);
    }
}