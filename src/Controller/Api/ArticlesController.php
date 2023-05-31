<?php
namespace App\Controller\Api;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ArticleRepository;
use App\Entity\Article;
use App\Service\ApiRegister;

class ArticlesController extends AbstractController {
    #[Route('/articles/all', name:'app_articles_all')]
    public function getAllArticles():Response {
        return $this->render('api/index.html.twig', [
        ]);
    }
    #[Route('/articles/one/{id}', name:'app_articles_all')]
    public function getOneArticle(string $id):Response {
        return $this->render('api/article.html.twig', [
        ]);
    }
}
?>