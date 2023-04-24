<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('prenom', 'Prénom'),
            TextField::new('nom', 'Nom'),
            EmailField::new('email', 'Adresse email'),
            TextField::new('password', 'Mot de passe'),
            ChoiceField::new('roles', 'Rôle(s)')->allowMultipleChoices(),
            ChoiceField::new('roles')->renderAsBadges([
                'ROLE_USER' => 'success',
                'ROLE_ADMIN' => 'warning',
            ])
        ];
    }
}
