<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('dateCommande', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('datePrestation', DateType::class, [
                'widget' => 'single_text',
            ])
            ->add('heurePrestation', TimeType::class, [
                'widget' => 'single_text',
            ])
            ->add('adresseLivraison', TextType::class)
            ->add('villeLivraison', TextType::class)
            ->add('distanceKm', IntegerType::class)
            ->add('prixLivraison', IntegerType::class)
            ->add('nbPersonnes', IntegerType::class)
            ->add('prixTotal', IntegerType::class)
            ->add('statutActuel', TextType::class)
        ;
        // ❌ On NE met PAS :
        // - utilisateur
        // - menu
        // - avis
        //
        // Ces champs doivent être gérés dans le contrôleur :
        // $commande->setUtilisateur($this->getUser());
        // $commande->setMenu($menu);
        // $commande->setAvis($avis);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
