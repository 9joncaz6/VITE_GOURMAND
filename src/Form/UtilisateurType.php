<?php

namespace App\Form;

use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UtilisateurType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Prénom',
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
            ])
            ->add('gsm', TelType::class, [
                'label' => 'Téléphone',
                'required' => false,
            ])
            ->add('adressePostale', TextType::class, [
                'label' => 'Adresse postale',
                'required' => false,
            ]);
        // ❗ On NE met PAS :
        // - roles
        // - actif
        // - password
        // Ces champs sont gérés par l’admin ou par un formulaire dédié.
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Utilisateur::class,
        ]);
    }
}
