<?php

namespace App\Form;

use App\Entity\Article; // on importe toutes les entités qu'on va utiliser dans nos méthodes
use App\Entity\Category;
use App\Entity\User;
// use Symfony\Bridge\Doctrine\Form\Type\EntityType as TypeEntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType; //on importe tous les composants pour les utiliser dans le builder du formulaire
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class, [
                'label' => 'Nom de l\'article',
                'attr'=>['class' => 'form-control mb-3'],
                'required'=>true
            ]) //Voir les types dans la doc "form" de Symfony
            ->add('contenu', TextareaType::class, [
                'label' => 'Description de l\'article',
                'attr'=>['class' => 'form-control mb-3'],
                'required'=>true
            ]) // ::class est une autre manière d'instancier un objet de la classe précisée juste avant
            ->add('date', DateType::class, [
                'label' => 'Date',
                'attr'=>['class' => 'form-control mb-3'],
                'required'=>true
            ])
            ->add('categories', EntityType::class, array(
                'label' => 'Choisir une catégorie',
                'attr'=>['class' => 'form-control mb-3'],
                'class' => Category::class, 
                'multiple' => true, //obligatoirement à true pour une collection
                'expanded' => false, //poiur mettre des checkbox à la place de la liste déroulante
                'required' => false), [

            ]) //le formulaire va générer automatiquement des listes déroulantes contenant les catégories de la bases de données !!! Pense à créer le méthode __toString() dans l'entité correspondante
            ->add('User', EntityType::class,  [
                'label' => 'Chosir une utilisateur ',
                'attr'=>['class' => 'form-control'],
                'class' => User::class,
            ])//le formulaire va générer automatiquement des listes déroulantes contenant les users de la bases de données !!! Pense à créer le méthode __toString() dans l'entité correspondante
            ->add('Ajouter', SubmitType::class, [
                'attr'=>['class' => 'btn btn-success mt-5 mb-5 mx-auto']
            ]) //On ajoute manuellement un bouton de type 'submit'
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
