<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Plat;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
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
            ->add('stockDisponible', IntegerType::class, [
                'label' => 'Stock disponible'
            ])

            // ⭐ Regroupement esthétique des plats
            ->add('plats', EntityType::class, [
                'class' => Plat::class,
                'choice_label' => 'nom',
                'multiple' => true,
                'expanded' => true,
                'label' => 'Plats du menu',
                'group_by' => fn(Plat $plat) => ucfirst($plat->getType()),
            ])

            ->add('imageFile', FileType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'Image du menu'
            ])

            // ⭐ Champ caché pour retour automatique après création d’un plat
            ->add('menu_id', HiddenType::class, [
                'mapped' => false,
                'data' => $options['menu_id'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Menu::class,
            'menu_id' => null,
        ]);
    }
}
