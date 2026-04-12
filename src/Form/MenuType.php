<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Plat;
use App\Entity\Regime;
use App\Entity\Theme;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            // Informations principales
            ->add('titre', TextType::class, [
                'label' => 'Titre du menu',
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
            ])
            ->add('nbPersonnesMin', IntegerType::class, [
                'label' => 'Nombre minimum de personnes',
            ])
            ->add('prixBase', MoneyType::class, [
                'label' => 'Prix de base',
                'currency' => 'EUR',
            ])
            ->add('conditions', TextareaType::class, [
                'label' => 'Conditions',
            ])
            ->add('stockDisponible', IntegerType::class, [
                'label' => 'Stock disponible',
            ])

            // Relations
            ->add('theme', EntityType::class, [
                'class' => Theme::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un thème',
                'required' => false,
                'label' => 'Thème',
            ])
            ->add('regime', EntityType::class, [
                'class' => Regime::class,
                'choice_label' => 'nom',
                'placeholder' => 'Choisir un régime',
                'required' => false,
                'label' => 'Régime',
            ])
            ->add('plats', EntityType::class, [
                'class' => Plat::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Plats du menu',
            ])

            // Upload d’images
            ->add('images', FileType::class, [
                'label' => 'Images du menu',
                'multiple' => true,
                'mapped' => false,   // important : les fichiers ne sont pas stockés directement dans l'entité
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}