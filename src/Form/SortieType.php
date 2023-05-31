<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom')
            ->add('dateHeureDebut')
            ->add('dateLimiteInscription')
            ->add('nbInscriptionsMax')
            ->add('duree')
            ->add('infosSortie')
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'choice_label' => 'nom'
                /*'mapped'=>false*/
            ])
            //A faire plus tard : Piste QueryBuilder
            /*->add('', EntityType::class, [
                'class' => Ville::class,
                'choices' => $options['villes'], // Utilisez l'option 'villes'
                'choice_label' => 'nom', // Remplacez par la propriété appropriée de Ville
                'mapped'=>false
            ])*/
            ->add('lieu', EntityType::class,[
                'class' => Lieu::class,
                'choice_label' => 'nom'
            ])
            ->add('etat', EntityType::class, [
                'class' => Etat::class,
                'choice_label' => 'libelle'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sortie::class,
            'villes' => []
        ]);
    }
}
