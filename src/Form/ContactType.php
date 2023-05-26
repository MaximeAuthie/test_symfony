<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType; //on importe tous les composants pour les utiliser dans le builder du formulaire
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [ 
                'label' => 'Votre nom',
                'attr'=>['class' => 'form-control mb-3'],
                'required'=>true
            ])
            ->add('prenom', TextType::class, [ 
                'label' => 'Votre prénom',
                'attr'=>['class' => 'form-control mb-3'],
                'required'=>true
            ])
            ->add('mail', EmailType::class, [ 
                'label' => 'Votre adresse email',
                'attr'=>['class' => 'form-control mb-3'],
                'required'=>true
            ])
            ->add('objet', TextType::class, [ 
                'label' => 'Objet du message',
                'attr'=>['class' => 'form-control mb-3'],
                'required'=>true,
            ])
            ->add('date', DateType::class, [
                'label' => 'Date',
                'widget' => 'single_text',
                'attr'=>['class' => 'form-control mb-3'],
                'input' => 'datetime_immutable',
                'required'=>true
            ])
            ->add('contenu', TextareaType::class, [
                'label' => 'Saisir votre message',
                'attr'=>['class' => 'form-control mb-3'],
                'required'=>true
            ])
            ->add('Envoyer', SubmitType::class, [
                'attr'=>['class' => 'btn btn-success mt-5 mb-5 mx-auto']
            ]) //On ajoute manuellement un bouton de type 'submit'
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contact::class,
        ]);
    }
}
