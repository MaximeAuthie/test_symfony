<?php
namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use App\Entity\Article;
use App\Service\ApiRegister;


class ApiArticlesController extends AbstractController {
    #[Route('/api/articles/get/all', name:'app_api_articles_all')]
    public function getAllArticles(ArticleRepository $articleRepository, ApiRegister $apiRegister, Request $request):Response {
        //Récupérer les variables à passer en paramètre de la méthode verifyToken() de ApiRegister
        $key = $this->getParameter('token');
        $jwt = $request->server->get('HTTP_AUTHORIZATION');
        $jwt = str_replace('Bearer ', '', $jwt);

        //On vérifie que le json a bien envoyé un token
        if ($jwt == '') {
            return $this->json(
                ['Erreur' => 'Le token n\'existe pas.'],
                400, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], 
                []
            );
        }

        //Exécuter méthode verifyToken() de ApiRegister et stocker son résultat dans une variable
        $verify = $apiRegister->verifyToken($jwt, $key);

        //Vérifier le retour de la méthode verifyToken()
        if ($verify === true) {

            //Récupérer la liste des articles
            $articlesList = $articleRepository->findAll();

            //Vérifier s'il existe des articles 
            if (!$articlesList) {
                return $this->json(
                    ['Erreur' => 'Il n\'a pas d\'article dans la BDD'],
                    206,
                    ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], 
                    []
                );
            }

            // Sinon, on a bien des articles remontés dans $articleList
            return $this->json(
                $articlesList, 
                200, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], //renvoie du json, uniquement depuis local host, et uniquelent sous forme de GET
                ['groups' => 'article:readAll']
            );
        } else {
            //Si le retour de la méthode verifyToken() est différent de true (si elle renvoie une erreur)
            return $this->json(
                ['Erreur' => $verify],
                400, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], 
                []
            );
        }
    }
    #[Route('/api/articles/get/{id}', name:'app_api_articles_all')]
    public function getOneArticle(ArticleRepository $articleRepository, ApiRegister $apiRegister, Request $request, string $id):Response {
        //Récupérer les variables à passer en paramètre de la méthode verifyToken() de ApiRegister
        $key = $this->getParameter('token');
        $jwt = $request->server->get('HTTP_AUTHORIZATION');
        $jwt = str_replace('Bearer ', '', $jwt);

        //On vérifie que le json a bien envoyé un token
        if ($jwt == '') {
            return $this->json(
                ['Erreur' => 'Le token n\'existe pas.'],
                400, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], 
                []
            );
        }

        //Exécuter méthode verifyToken() de ApiRegister et stocker son résultat dans une variable
        $verify = $apiRegister->verifyToken($jwt, $key);

        //Vérifier le retour de la méthode verifyToken()
        if ($verify === true) {

            //Récupérer la liste des articles
            $article = $articleRepository->find($id);

            //Vérifier si l'article existe
            if (!$article) {
                return $this->json(
                    ['Erreur' => 'L\'article demandé n\'existe pas'],
                    206,
                    ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], 
                    []
                );
            }

            // Sinon, on a bien un article
            return $this->json(
                $article, 
                200, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], //renvoie du json, uniquement depuis local host, et uniquelent sous forme de GET
                ['groups' => 'article:readAll']
            );
        } else {
            //Si le retour de la méthode verifyToken() est différent de true (si elle renvoie une erreur)
            return $this->json(
                ['Erreur' => $verify],
                400, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], 
                []
            );
        }
    }
}
?>