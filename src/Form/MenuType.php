<?php

namespace App\Form;

use App\Entity\Menu;
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
            ->add('titre', TextType::class, [
                'label' => 'Titre du menu'
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description'
            ])
            ->add('nbPersonnesMin', IntegerType::class, [
                'label' => 'Nombre minimum de personnes'
            ])
            ->add('prixBase', MoneyType::class, [
                'label' => 'Prix de base'
            ])
            // 🔥 Champ supprimé car il n'existe plus dans l'entité
            // ->add('conditions', TextareaType::class, [
            //     'required' => false,
            //     'label' => 'Conditions'
            // ])
            ->add('stockDisponible', IntegerType::class, [
                'label' => 'Stock disponible'
            ])
            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Image du menu'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
        ]);
    }
}
