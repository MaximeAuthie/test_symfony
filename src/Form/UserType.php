<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType; // à importer pour pouvoir spécifier le type de chaque champs
use Symfony\Component\Form\Extension\Core\Type\TextType; // à importer pour pouvoir spécifier le type de chaque champs
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', EmailType::class, [ // faire ctrl+i sur le Type::class pour afficher le use et cliquer dessus pour le mettre directement ci-dessus
                'label' => 'Votre mail',
                'attr'=>['class' => 'form-control'],
                'required'=>true
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'Saisir un mot de passe', 'hash_property_path' => 'password'],
                'second_options' => ['label' => 'Saisir à nouveau votre mot de passe'],
                'attr'=>['class' => 'form-control'],
                'mapped' => false,
            ])
            ->add('nom', TextType::class, [
                'label' => 'Votre nom',
                'attr'=>['class' => 'form-control'],
                'required'=>true
            ])
            ->add('prenom', TextType::class, [
                'label' => 'Votre prénom',
                'attr'=>['class' => 'form-control'],
                'required'=>true
            ])
            ->add('Valider', SubmitType::class, [
                'attr'=>['class' => 'btn btn-success mt-5 mb-5 mx-auto']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
