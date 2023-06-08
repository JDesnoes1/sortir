<?php

namespace App\Form;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Sortie;
use App\Entity\Ville;
use Doctrine\DBAL\Types\StringType;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SortieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', null, [
                'label' => 'Nom : '
            ])
            ->add('dateHeureDebut', DateTimeType::class, [
                'label' => 'Début : ',
                'widget'=>'single_text',
                'html5'=>true
            ])
            ->add('dateLimiteInscription', DateType::class, [
                'label' => 'Date limite d\'inscription : ',
                'widget'=>'single_text',
                'html5'=>true
            ])
            ->add('nbInscriptionsMax', null, [
                'label' => 'Nb inscriptions max : '
            ])
            ->add('duree', null, [
                'label' => 'Durée : '
            ])
            ->add('infosSortie', null, [
                'label' => 'Infos Sortie : '
            ])
            ->add('campus', EntityType::class, [
                'class' => Campus::class,
                'label' => 'Campus : ',
                'disabled' => true,
                'choice_label' => 'nom'
                /*'mapped'=>false*/
            ])
            ->add('ville', EntityType::class, [
                'label' => 'Villes : ',
                'class' => Ville::class,
                'choices' => $options['villes'],
                'choice_label' => 'nom',
                'mapped' => false,
                'placeholder'=> 'Sélectionnez une ville'
            ])
            ->add('lieu', EntityType::class,[
                'label' => 'Lieu : ',
                'class' => Lieu::class,
                'choice_label' => 'nom',
                'placeholder'=> 'Sélectionnez un lieu'
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
