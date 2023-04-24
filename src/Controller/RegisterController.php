<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request; //permet de récupérer tout ce qui est submit dans un form ou de récupérer des fichiers json.
use App\Repository\UserRepository; // On importe le repository correspondant pour pouvoir utiliser ses méthodes de consultation
use Doctrine\ORM\Mapping\Id;
use Symfony\Component\Filesystem\Path;
use App\Form\UserType; //Liaison du formulaire
use App\Entity\User; //Liaison de l'entité article
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Utils; //on importe le fichier custom avec les fonctions utilitaires
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; //on importe une classe pour hasher le MDP

class RegisterController extends AbstractController
{
    // #[Route('/register', name: 'app_register')]
    // public function index(): Response
    // {
    //     return $this->render('register/index.html.twig', [
    //         'controller_name' => 'RegisterController',
    //     ]);
    // }


    #[ROUTE('/register', name:"app_register")] // Route du formulaire pour ajouter un article
    public function addUser(EntityManagerInterface $em, Request $request, UserRepository $repo, UserPasswordHasherInterface $hash) {
        $message=null;
        $user = new User(); //instancier un objet User dans lequel on va stocker le retour du formulaire
        $form = $this->createForm(UserType::class, $user); //Création d'une instance du formulaire
        $form->handleRequest($request); //Récupération des datas du formulaire dans l'instance qu'on vient de créer (ici $form)

        if ($form->isSubmitted() AND $form->isValid()) { //on teste si le formulaire est bien submit et conforme au modèle de formulaire
            $recup = $repo->findOneBy(['nom'=>$user->getEmail()]); //requête pour récupéré une éventuelle catégorie avec la même adresse mail dans la BDD

            if (!$recup) { //si la valeur de $récup est à nul

                //Nettoyage des inputs
                $pass = Utils::cleanInputStatic( $request->request->all('user')['password']['first']); //on récupère le MDP saisi dans le formulaire // on utilise la méthode cleanInputStatic de la casse Utils pour nettoyer le code
                $password = $hash->hashPassword($user, $pass); // On crypte le MDP ($pass) et on le met dans l'instance $user // on utilise la méthode cleanInputStatic de la casse Utils pour nettoyer le code
                $nom = Utils::cleanInputStatic( $request->request->all('user')['nom']);// on utilise la méthode cleanInputStatic de la casse Utils pour nettoyer le code
                $prenom = Utils::cleanInputStatic( $request->request->all('user')['prenom']);// on utilise la méthode cleanInputStatic de la casse Utils pour nettoyer le code
                $mail = Utils::cleanInputStatic( $request->request->all('user')['email']);// on utilise la méthode cleanInputStatic de la casse Utils pour nettoyer le code

                //On va ensuite setter les variables nettoyées dans l'instance $user
                $user->setNom($nom);
                $user->setPrenom($prenom);
                $user->setEmail($mail);
                $user->setPassword($password);
                $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']); // ici on set par défaut les 2 rôles à l'instance $user.

                $em->persist($user); //on fait persister les données
                $em->flush(); //on envoie les données en BDD
                $message = 'Bienvenue '.$user->getPrenom().'. Votre compte a bien été créée';
            } else {
                $message = 'L\'adresse mail'.$user->getEmail().'est déjà associée à un compte existant';
            }
            
        }
        
        return $this->render('register/userAdd.html.twig', [
            'form'=>$form->createView(), // ici on fait passer l'objet $form en lui appliquant la méthode createView, qui permet de générer l'interface.
            'message'=> $message
        ]);
    }
}
