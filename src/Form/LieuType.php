<?php

namespace App\Form;

use App\Entity\Lieu;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LieuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('rue', TextType::class, [
                'attr' => [
                    'class' => 'autocomplete-input',
                    'autocomplete' => 'off',
                ],
                'required' => true,
            ])
            ->add('latitude', TextType::class, [
                'required' => false
            ])
            ->add('longitude', TextType::class, [
                'required' => false,
            ])
            ->add('ville', EntityType::class,[
                'class' => Ville::class,
                'choice_label' => 'nom'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Lieu::class,
        ]);
    }
}
