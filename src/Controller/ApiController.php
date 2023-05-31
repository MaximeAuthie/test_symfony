<?php

namespace App\Controller;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Service\ApiRegister;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use function PHPUnit\Framework\isEmpty;

class ApiController extends AbstractController
{
    #[Route('/api', name: 'app_api')]
    public function index(): Response
    {
        return $this->render('api/index.html.twig', [
            'controller_name' => 'ApiController',
        ]);
    }

    #[Route('/api/register', name: 'app_user_register', methods: 'GET')]
    public function verifConnexion(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, ApiRegister $apiRegister, UserRepository $userRepository): Response
    {
        try {
            // On récupère les données du GET
            $userEmail = $request->query->get('email');
            $userPassword = $request->query->get('password');

            // On appelle la méthode authentification du service Apiregister
            if ($apiRegister->authentification($userPasswordHasherInterface, $userRepository, $userEmail, $userPassword)) {
                
                return $this->json(
                    ['Connexion' => 'OK'],
                    200, 
                    ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], 
                    []
                );
            } else {
                return $this->json(
                    ['Connexion' => 'Invalide'],
                    206, 
                    ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], 
                    []
                );
            }

        } catch (\Exception $error) {
            return $this->json(
                ['Erreur' => $error->getMessage()],
                $error->getCode(), 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], 
                []
            );
        }
    }

    #[Route('/api/identification', name: 'app_api_identification ', methods: 'POST')]
    public function getToken(Request $request, UserPasswordHasherInterface $userPasswordHasherInterface, ApiRegister $apiRegister, UserRepository $userRepository, SerializerInterface $serializerInterface)
    {
        try {
            // On récupère le fichier json
            $json = $request->getContent();
            
            // On vérifie que le json n'est pas vide
            if (!$json) {
                return $this->json(
                    ['Erreur' => 'Le json est vide ou n\'esiste pas.'],
                    400,
                    ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'localhost', 'Access-Control-Allow-Method' => 'GET'], 
                    [] ); 
            }

            //On transforme le json en tableau grace à SerializerInterface
            $data= $serializerInterface->decode($json,'json');
        
            //On récupère les datas du json
            $userEmail= $data['email'];
            $userPassword= $data['password'];

            // On récupère la clé de chiffrement
            $key = $this->getParameter('token');

            // On teste si les variables $userMail et $user password sont vides
            if (!empty($userEmail) AND !empty($userPassword)) {
             
                // On appelle la méthode authentification du service ApiRegister
                if ($apiRegister->authentification($userPasswordHasherInterface, $userRepository, $userEmail, $userPassword)) {

                    // Si la méthode d'authentification retourne 'true', on appelle la méthode genToken du service ApiRegister pour récupérer un token valide
                    $token = $apiRegister->genToken($userEmail, $key, $userRepository);
                
                    return $this->json(
                        $token,
                        200, 
                        ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], 
                        []
                    );
                } else {
                    
                    // Si l'authentification n'est pas valide, on retourne ce json
                    return $this->json(
                        ['Erreur' => 'Connexion invalide'],
                        401, 
                        ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], 
                        []
                    );
                }    
            } else {
                return $this->json(
                    ['Erreur' => 'Données manquantes'],
                    400, 
                    ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], 
                    []
                );
            }

        } catch (\Exception $error) {
            return $this->json(
                ['Erreur' => $error->getMessage()],
                400, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], 
                []
            );
        }
    }

    #[Route('/api/identification/test_token', name: 'app_api_test_token ', methods: 'GET')]
    public function testToken(Request $request, ApiRegister $apiRegister)
    {
        // On récupère le token depuis la requête
        $token = $request->server->get('HTTP_AUTHORIZATION');
        $token = str_replace('Bearer ', '', $token);
     
        // On récupère la clé de chiffrement dans les variables d'environnement
        $key = $this->getParameter('token');
       
        // On vérifie le token avec la méthode verifyToken() du service ApiRegister
        if ($apiRegister->verifyToken($token, $key) === true) {
    
            return $this->json(
                ['Authentification' => 'OK'],
                200, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], 
                []
            );
        } else {
            return $this->json(
                ['Authentification' => 'Erreur'],
                200, 
                ['Content-Type'=>'application/json','Access-Control-Allow-Origin' =>'*', 'Access-Control-Allow-Method' => 'GET'], 
                []
            );
        }

    }

    #[Route('/api/localToken', name: 'app_api_local_token ')]
    public function localToken():Response {
        return $this->render('api/local.html.twig');
    }
}
