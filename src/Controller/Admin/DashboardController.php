<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator; //! A ajouter manuellement
use App\Entity\Article; //!Importer toutes les entités auxquelles on va accéder dans l'admin
use App\Entity\Category;
use App\Entity\User;

class DashboardController extends AbstractDashboardController
{
    #[Route('/prout', name: 'prout')] //! Par sécurité changer la route /admin et la modifier aussi dans security.yaml
    public function index(): Response
    {
        $url = $this->adminUrlGenerator
        ->setController(ArticleCrudController::class)
        ->generateUrl();
        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Test Projet J2');
    }

    public function configureMenuItems(): iterable //! Ici on paramètre le menu de droite du panel admin
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home', 'app_home'); //! On spécifie le nom de la route (app_home) pour retourner vers la page d'accueil du site client
        yield MenuItem::linkToCrud('Articles', 'fas fa-list', Article::class); //! ('nom du menu','icone font awesome', classe)
        yield MenuItem::linkToCrud('Catérogies', 'fas fa-newspaper', Category::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-users', User::class);
    }
    public function __construct(private AdminUrlGenerator $adminUrlGenerator){} //! A rajouter manuellement
}
