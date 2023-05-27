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
use Doctrine\ORM\EntityManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface; //on importe une classe pour hasher le MDP
use App\Service\Messagerie;

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
    public function addUser(EntityManagerInterface $em, Request $request, UserRepository $repo, UserPasswordHasherInterface $hash, Messagerie $messagerie) {
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
                $user->setActivate(false);

                $em->persist($user); //on fait persister les données
                $em->flush(); //on envoie les données en BDD

                // On récupère les variables d'authentification du serveur mail pour utiliser la méthode sendMail() de la classe Messagerie
                $login = $this->getParameter('accountmail'); //getParamater() est une méthode de la classe native AbstractController
                $password = $this->getParameter('passmail');

                // Variable pour le mail
                $object = 'Activation de votre compte';
                $content = '<p>Pour activiver votre compte, veuiller cliquer sur le lien ci-dessous.</p>'.
                '<a href="https://127.0.0.1:8000/register/activate/'.$user->getId().'">Activation</a>';
                
                $mailStatus = $messagerie->sendMail($login, $password, $mail, $object, $content);

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

    #[ROUTE('/register/activate/{id}', name:"app_register_activate")] // Route du pour activer un compte via un email de confirmation
    public function activateuser($id, UserRepository $userRepository, EntityManagerInterface $entityManager) {
        $message = '';

        // On récupère l'utilisateur via son ID
        $user = $userRepository->find($id);

        // On vérifie si le compte est déjà activé 
        if ($user) {
        
            if ($user->isActivate() == false) {
                
                // On set la propriété à true
                $user->setActivate(true);

                // On tente de persister les données et de flush
                try {
                    $entityManager->persist($user);
                    $entityManager->flush();

                    $message = "Votre compte a été activé avec succès";
                    

                // En cas d'erreur, on récupère l'exception et on retourne le message d'erreur
                } catch (\Exception $error) {
                    return $error->getMessage();
                }

            } else {
                // Si l'utilisateur est déjà activé
                $message = 'Votre compte a déjà été activé';
            }
        } else {

            //Si l'utilisateur n'existe pas, on redirige vers la page d'inscription
            return $this->redirectToRoute('app_register');

        }

        return $this->render('register/userActivated.html.twig', [
            'message'=> $message
        ]); 
    }

    //fonction qui envoi le mail d'activation si l'utilisateur tente de se connecter sans être activé (redirigé depuis AuthAuthenticator.php)
    #[Route('/sendMail/activate/{id}', name:'app_send_activate')]
    public function sendMailActivate(Utils $utils, 
    Messagerie $messagerie, UserRepository $repo,$id):Response{
        //nettoyage de l'id
        $id = $utils->cleanInput($id);
        //récupération des identifiant de messagerie
        $login = $this->getParameter('accountmail');
        $mdp = $this->getParameter('passmail');
        //variable qui récupére l'utilisateur
        $user = $repo->find($id);
        if($user){
            $objet = 'activation du compte';
            $content = mb_convert_encoding('<p>Vous avez tenté de vous connecter mais votre compte n\'est pas encore activé.</p>'.
            '<p>Pour activiver votre compte, veuiller cliquer sur le lien ci-dessous.</p>'.
            '<a href="https://127.0.0.1:8000/register/activate/'.$user->getId().'">Activation</a>', 'ISO-8859-1', 'UTF-8');
            //on stocke la fonction dans une variable
            $status = $messagerie->sendMail($login, $mdp,$user->getEmail(), $objet, $content );

            //ON redirige vers la fonction login et affichage de l'erreur compte non activé
            return $this->redirectToRoute('app_login',['error'=>1]);
        }
        else{
            return new Response('Le compte n\'existe pas', 200, []);
        }
    }
}
