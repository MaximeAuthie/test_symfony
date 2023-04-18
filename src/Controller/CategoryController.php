<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request; //permet de récupérer tout ce qui est submit dans un form ou de récupérer des fichiers json.
use Symfony\Component\Routing\Annotation\Route;
use App\Form\CategorieType;
use App\Repository\CategoryRepository; //pour interagir avec la BDD
use App\Entity\Category; //modèle d'objet catégorie
use Doctrine\ORM\EntityManagerInterface;

class CategoryController extends AbstractController
{
    #[Route('/category', name: 'app_category')]
    public function index(): Response
    {
        return $this->render('category/index.html.twig', [
            'controller_name' => 'CategoryController',
        ]);
    }

    #[Route('/category/all', name: 'app_category_all')]
    public function showAllCategories(CategoryRepository $categoryRepository):Response { //on passe le repository en param et on le stocke dans une variable pour instancier la classe du repository
        $message=null;
        //Récupérer dans un tableau toutes les catégories
        $categories = $categoryRepository->findAll(); //on utilise un méthode du repository pour récupérer toutes les catégories

        if (!$categories) {
            $message = 'Aucune catégorie à afficher.';
        } else {
            return $this->render('category/categoryList.html.twig', [ //render permet de rendre un fichier.twig en .html
                'liste'=>$categories,
                'message'=>$message
            ]);
        }

        
    }

    #[ROUTE('/category/add', name:"app_category_add")]
    public function addCategory(EntityManagerInterface $em, Request $request, CategoryRepository $repo) {
        $message=null;
        $category = new Category(); //instancier un objet Category dans lequel on va stocker le retour du formulaire
        $form = $this->createForm(CategorieType::class, $category); //Création d'une instance du formulaire
        $form->handleRequest($request); //Récupération des datas du formulaire dans l'intance qu'on vient de créer

        if ($form->isSubmitted() AND $form->isValid()) { //on teste si le formulaire est bien submit et conforme au modèle de formulaire
            $recup = $repo->findOneBy(['nom'=>$category->getNom()]); //requête pour récupéré une éventuelle catégorie avec le même nom dans la BDD

            if (!$recup) { //si la valeur de $récup est à nul
                $em->persist($category); //on fait persister les données
                $em->flush(); //on envoie les données en BDD
                $message = 'La catégorie '.$category->getNom().' a bien été créée';
            } else {
                $message = 'La catégorie "'.$category->getNom().'" existe déjà. Création annulée';
            }
            
        }
        
        return $this->render('category/categoryAdd.html.twig', [
            'form'=>$form->createView(), // ici on fait passer l'objet $form en lui appliquant la méthode createView, qui permet de générer l'interface.
            'message'=> $message
        ]);
    }

    #[ROUTE('/category/update/{id}', name:"app_category_update")]
    public function updateCategory (int $id, CategoryRepository $categoryRepository, EntityManagerInterface $em, Request $request):Response { // ici on a besoin de Category Repository pour rechercher l'artivle existant
        $message=null;
        $category = $categoryRepository->find($id); //objet qu'on va modifier (on le recharche grace à la fonction find de Category Repository)
        $form = $this->createForm(CategorieType::class, $category); //Création d'une instance du formulaire
        $form->handleRequest($request); //Récupération des datas du formulaire
        if ($form->isSubmitted() AND $form->isValid()) {
            $em->persist($category); //on fait persister les données
            $em->flush(); //on envoie les données en BDD
            $message = 'La catégorie "'.$category->getNom().'" a bien été modifiée';
        } 
        return $this->render('category/categoryUpdate.html.twig', [
            'form'=>$form->createView(), // ici on fait passer l'objet $form en lui appliquant la méthode createView, qui permet de générer l'interface.
            'message'=>$message
        ]); 
    }

    #[ROUTE('/category/delete/{id}', name:"app_category_delete")]
    public function deleteCategory(int $id, CategoryRepository $repo, EntityManagerInterface $em):Response { // pas besoin de request dans ce cas car on envoie pas de datas à insérer
        $message=null;
        $categorie = $repo->find($id);
        $em->remove($categorie);
        $em->flush();
        //! Attention, la suppression en cascade dans la table d'association se fait toute seule grace à Symfony
        return $this->redirectToRoute('app_category_all');
    }
}
