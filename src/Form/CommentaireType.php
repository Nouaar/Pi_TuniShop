<?php

namespace App\Form;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use App\Entity\Blog;
use App\Entity\Commentaire;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('text', TextareaType::class, [
            'label' => 'Your Comment',
            'attr' => ['rows' => 4, 'placeholder' => 'Write your comment here...']
        ])
            ->add('submit', SubmitType::class, [
                'label' => 'Submit Comment',
                'attr' => ['class' => 'btn btn-primary mt-3']
            ]);


    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}
