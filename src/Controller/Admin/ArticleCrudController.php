<?php

namespace App\Controller\Admin;

use App\Entity\Article;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\Text;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField; //! La liste des composants de formulaire à importer est dans la doc symfony
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;


class ArticleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Article::class;
    }

     //! Commenté par défaut. A décommenter pour personnaliser les champs du formulaire admin de création d'un nouvel article
     //Beacoup d'option de personnalisation dans la doc Symfony
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('titre', 'Titre'),
            TextareaField::new('contenu', 'Contenu'),
            AssociationField::new('categories', 'Catégories'),
            DateField::new('date', 'Date'),
            AssociationField::new('User', 'Utilisateur'),
        ];
    }
    
}
    