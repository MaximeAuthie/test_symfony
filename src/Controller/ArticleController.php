<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request; //permet de récupérer tout ce qui est submit dans un form ou de récupérer des fichiers json.
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository; // On importe le repository correspondant pour pouvoir utiliser ses méthodes de consultation
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Filesystem\Path;
use App\Form\ArticleType; //Liaison du formulaire
use App\Entity\Article; //Liaison de l'entité article
use Doctrine\ORM\EntityManagerInterface;

class ArticleController extends AbstractController //permet par exemple de récupérer la fonction "render" pour l'uiliser sur notre instance de ArticleController
{
    #[Route('/article', name: 'app_article')]
    public function index(): Response
    {
        $tva= $this->getParameter('tva'); //! Ici on récupère une variable d'envoironnement pour test
        $auteur= $this->getParameter('auteur'); //! Ici on récupère une variable d'envoironnement pour test
        return $this->render('article/index.html.twig', [
            'data1'=>$tva,
            'data2'=>$auteur
        ]);
    }
    #[Route('/article/all', name: 'app_article_all')]
    public function showAllArticles(ArticleRepository $articleRepository):Response { //on passe le repository en param et on le stocke dans une variable pour instancier la classe du repository
        //Récupérer dans un tableau tous les articles
        $articles = $articleRepository->findAll(); //on utilise un méthode du repository pour récupérer tous les articles
        // dd($articles); //tue le code (arrête l'execution du code et dump la variable)
        return $this->render('article/index2.html.twig', [ //render permet de rendre un fichier.twig en .html
            'liste'=>$articles,
        ]);
    }
    #[ROUTE('/article/id/{id}', name: 'app_article_id')]
    public function showArticleById(ArticleRepository $articleRepository, int $id):Response { // on passe une variable $id (même nom que le param dans la route) en param pour récupérer directement l'id de la route
        //récupérer l'article depuis son id
        $article = $articleRepository->find($id);

        //retourner une interface twig pour afficher l'article récupéré
        return $this->render('article/article.html.twig', [
            'article'=>$article,
        ]);
    }
    #[ROUTE('article/add', name:"app_article_add")] // Route du formulaire pour ajouter un article
    public function addArticle (EntityManagerInterface $em, Request $request, ArticleRepository $repo):Response {
        $message=null;
        $article = new Article(); //objet dans lequel on va stocker le retour du formulaire
        $form = $this->createForm(ArticleType::class, $article); //Création d'une instance du formulaire
        $form->handleRequest($request); //Récupération des datas du formulaire
        if ($form->isSubmitted() AND $form->isValid()) {
            $recup = $repo->findOneBy(['titre'=>$article->getTitre()]); //On utilise la méthode findOneBy() de l'instance ArticleRepository pour regarder si un article avec le même nom exite déjà dans la BDD

            if (!$recup) {
                $em->persist($article); //on fait persister les données
                $em->flush(); //on envoie les données en BDD
                $message = 'L\'article '.$article->getTitre().' a bien été créée';
            } else {
                $message = 'L\'article "'.$article->getTitre().'" existe déjà. Création annulée';
            }
            
        }
        return $this->render('article/articleAdd.html.twig', [
            'form'=>$form->createView(), // ici on fait passer l'objet $form en lui appliquant la méthode createView, qui permet de générer l'interface.
            'message'=>$message
        ]); 
    }

    #[ROUTE('article/update/{id}', name:"app_article_update")]
    public function updateArticle (int $id, ArticleRepository $articleRepository, EntityManagerInterface $em, Request $request):Response { // ici on a besoin de Article Repository pour rechercher l'artivle existant
        $message=null;
        $article = $articleRepository->find($id); //objet qu'on va modifier (on le recharche grace à la fonction find de Article Repository)
        $form = $this->createForm(ArticleType::class, $article); //Création d'une instance du formulaire
        $form->handleRequest($request); //Récupération des datas du formulaire
        if ($form->isSubmitted() AND $form->isValid()) {
            $em->persist($article); //on fait persister les données
            $em->flush(); //on envoie les données en BDD
            $message = 'L\'article "'.$article->getTitre().'" a bien été modifié';
        } 
        return $this->render('article/articleUpdate.html.twig', [
            'form'=>$form->createView(), // ici on fait passer l'objet $form en lui appliquant la méthode createView, qui permet de générer l'interface.
            'message'=>$message
        ]); 
    }
}
