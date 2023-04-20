<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Article;
use App\Entity\Category;
use App\Entity\User;
use App\Repository\ArticleRepository;
use App\Repository\CategoryRepository; //pour utiliser la méthode findOneBy de l'entité Category
use App\Repository\UserRepository; //pour utiliser la méthode findOneBy de l'entité User
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ApiArticleController extends AbstractController 
{
    #[ROUTE('api/article/all', name:"app_api_article_all", methods: 'GET')] //api/nom de l'entité/action
    public function getArticles(ArticleRepository $repo):Response { //on passe ArticleRepository en param pour pouvoir utiliser findAll()
        $articles = $repo->findAll();
        if (!$articles) {
            return $this->json(
                ['Erreur' => 'Il n\'a pas d\'article dans la BDD'],
                 206, //!sVoir la liste de code erreur html sud wikipedia
                 [], 
                 [] ); //passer le curseur sur 'json' pour voir le détail des paramètres à passer 
        }
        return $this->json(
            $articles, 
            200, 
            ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], //renvoie du json, uniquement depuis local host, et uniquelent sous forme de GET
            ['groups' => 'article:readAll']); //passer le curseur sur 'json' pour voir le détail des paramètres à passer
    }

    #[ROUTE('api/article/{id}', name:"app_api_article_id", methods: 'GET')] //api/nom de l'entité/action
    public function getArticlesById(int $id, ArticleRepository $repo):Response { //on passe ArticleRepository en param pour pouvoir utiliser findAll()
        $article = $repo->find($id);
        if (empty($article)) {
            return $this->json(
                ['Erreur' => 'Cet article n\'existe pas dans la BDD'],
                 206, //!sVoir la liste de code erreur html sud wikipedia
                 [], 
                 [] ); //passer le curseur sur 'json' pour voir le détail des paramètres à passer 
        }
        return $this->json(
            $article, 
            200, 
            ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], //renvoie du json, uniquement depuis local host, et uniquelent sous forme de GET
            ['groups' => 'article:readById']); //passer le curseur sur 'json' pour voir le détail des paramètres à passer
    }

    #[ROUTE('api/article/add', name:"app_api_article_add", methods: 'PUT')] //api/nom de l'entité/action
    public function addArticles(ArticleRepository $repo, CategoryRepository $repoCat, UserRepository $userRepo ,Request $request, SerializerInterface $serialize, EntityManagerInterface $em):Response {
        
        try { 
            //?Récupérer le contenu de la requête en provenance du front (tout ce qui se trouve dans le body de la requête)
            $json = $request->getContent();

            //?On vérifie si le json n'est pas vide
            if (!$json) {
                return $this->json(
                    ['Erreur' => 'Le json est vide ou n\'esiste pas.'],
                    400, //!Voir la liste de code erreur html sud wikipedia
                    ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], 
                    [] ); //passer le curseur sur 'json' pour voir le détail des paramètres à passer 
            }

            //?On sérialise le json (on le change de format json -> tableau en stockant le résultat dans une variable)
            $data = $serialize->decode($json, 'json'); //variable, format //! Voir la doc symfony qui explique très bien tout ça


            //?On vérifie si l'article existe déjà
            $recup = $repo-> findOneBy(['titre'=>$data['titre'], 'contenu'=>$data['contenu']]); //! On a plusieur critère, donc on met le tout dans un tableau
            if ($recup) { 
                //renvoyer un json d'erreur
                return $this->json(
                    ['Erreur' => 'L\'article '.$data['titre'].' existe déjà dans la BDD'],
                    206, //!sVoir la liste de code erreur html sud wikipedia
                    ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], 
                    [] ); //passer le curseur sur 'json' pour voir le détail des paramètres à passer 
            }

            //?Instancier un objet article
            $article = new Article();
            $article->setTitre($data['titre']);
            $article->setContenu($data['contenu']);
            $article->setDate(new \DateTimeImmutable($data['date'])); //On utilise une classe externe (d'où le \) pour le format de date
            

            //?On vérifie si des catégories ont été renseignées
            if (isset($data['categories'])) { 
                //boucle pour ajouter chacune des catégories saisies
                foreach ($data['categories'] as $value) {
                    $cat = $repoCat->findOneBy(['nom'=>$value['nom']]); //on récupère l'instance de l'objet Catégorie dans la BDD pour pouvoir ajouter un objet entier dans l'instance $article de Article
                    //Vérifier si la catégorie existe
                    if (!$cat) {
                        return $this->json( // A la première des catégorie de la liste qui n'existe pas, je sors de la méthode et je fais un retour au front
                            ['erreur'=> 'La catégorie '.$value['nom'].' n\'existe pas dans la BDD'],
                            400, 
                            ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], //renvoie du json, uniquement depuis local host, et uniquelent sous forme de GET
                            []);
                    } else {
                        $article->addCategory($cat); //on ajout la collection de catégory à l'instance $article
                    }
                }
                
            }

            //?on vérifie si l'API nous a envoyé un user
            if (isset($data['user'])) { 
                //On vérfie si user existe dans la BDD
                $user = $userRepo->findOneBy(['email'=>$data['user']['email']]);
                if (!$user) {
                    return $this->json( //Si le user n'existe pas, je sors de la méthode et je fais un retour au front
                        ['erreur'=> 'L\'ustilisateur '.$data['email'].' n\'existe pas dans la BDD'],
                        401, 
                        ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], //renvoie du json, uniquement depuis local host, et uniquelent sous forme de GET
                        []);
                } else {
                    $article->setUser($user); // on ajoute l'objet $user à l'instance $article
                }
            }

            //Persiter les données
            $em->persist($article);
            $em->flush();

            //Renvoyer un json pour avertir ue l'enregistrement à bien été effectué
            return $this->json(
                ['erreur'=> 'L\'article '.$article->getTitre().' à bien été ajouté à la BDD'],
                200, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], //renvoie du json, uniquement depuis local host, et uniquelent sous forme de GET
                []); //tableau vide car pas de groupe

        } catch (\Exception $error) { //gestion des autres erreurs non prévues avec du json (champs vide ou syntax error par exemple)
            return $this->json(
                ['erreur'=> 'Etat du json : '.$error->getMessage()],
                400, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], //renvoie du json, uniquement depuis local host, et uniquelent sous forme de GET
                []); //tableau vide car pas de groupe 
        }
    }

    #[ROUTE('api/article/delete', name:"app_api_article_json_delete", methods: 'DELETE')] //Méthode pour supprimer un article en avoyant son id dans un json au lieu de l'URL
    public function deleteArticleJson(ArticleRepository $articleRepo, EntityManagerInterface $em, Request $request, SerializerInterface $serialize ) {

        try {
            //récupérer le contenu du json
            $json = $request->getContent();

            //?On vérifie si le json n'est pas vide
            if (!$json) {
                return $this->json(
                    ['Erreur' => 'Le json est vide ou n\'esiste pas.'],
                    400, //!Voir la liste de code erreur html sud wikipedia
                    ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], 
                    [] ); //passer le curseur sur 'json' pour voir le détail des paramètres à passer 
            }

            //?On sérialise le json (on le change de format json -> tableau en stockant le résultat dans une variable)
            $data = $serialize->decode($json, 'json'); //variable, format //! Voir la doc symfony qui explique très bien tout ça

            //On recherche l'article dans la BDD
            $article = $articleRepo->findOneBy(['id'=>$data['id']]);

            //Si l'article demandé n'existe pas dans la BDD
            if (!isset($article)) {
                return $this->json(
                    ['erreur'=> 'L\'article N°'.$data['id'].' n\'existe pas dans la BDD.'],
                    400, 
                    ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], //renvoie du json, uniquement depuis local host, et uniquelent sous forme de GET
                    []);
            }

            //Si l'article existe dans la BDD
                $em->remove($article);
                $em->flush();
                return $this->json(
                    ['erreur'=> 'L\'article '.$article->getTitre().' a bien été supprimé de la BDD.'],
                    200, 
                    ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], //renvoie du json, uniquement depuis local host, et uniquelent sous forme de GET
                    []);

        } catch (\Exception $error) { //Gestion des erreurs inattendues
            return $this->json(
                ['erreur'=> 'Erreur : '.$error->getMessage()],
                500, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], //renvoie du json, uniquement depuis local host, et uniquelent sous forme de GET
                []); 
        }
        
    }

    #[ROUTE('api/article/delete/{id}', name:"app_api_article_delete", methods: 'DELETE')]
   
}
?>
