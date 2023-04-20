<?php

namespace App\Form;

use App\Entity\Category;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType; // à importer pour pouvoir spécifier le type de chaque champs
use Symfony\Component\Form\Extension\Core\Type\TextType; // à importer pour pouvoir spécifier le type de chaque champs

class CategorieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class , [
                'label' => 'Nom de la catégorie',
                'attr'=>['class' => 'form-control'],
                'required'=>true
            ])
            // ->add('articles')
            ->add('Valider', SubmitType::class, [
                'attr'=>['class' => 'btn btn-success mt-5 mb-5 mx-auto']
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Category::class,
        ]);
    }
}
