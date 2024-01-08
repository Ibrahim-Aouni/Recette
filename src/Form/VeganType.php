<?php

namespace App\Form;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class VeganType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('imageName')
            ->add('time')
            ->add('nbPeople')
            ->add('difficulty')
            ->add('description')
            ->add('price')
            ->add('isFavorite')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('isPublic')
            ->add('ingredients', EntityType::class, [
                'class' => Ingredient::class,
'choice_label' => 'id',
'multiple' => true,
            ])
            ->add('user', EntityType::class, [
                'class' => User::class,
'choice_label' => 'id',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Recette::class,
        ]);
    }
}
