<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Contact;
use App\Form\ContactType;
use App\Repository\ContactRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Utils;
use App\Service\Messagerie; //Pour utiliser la méthode sendMail() de la classe Messagerie
use Doctrine\ORM\EntityManager;

class ContactController extends AbstractController {
    #[Route('/contact', name: 'app_contact')]
    public function index(): Response
    {
        return $this->render('contact/index.html.twig', [
            'controller_name' => 'ContactController',
        ]);
    }

    #[Route('/contact/form', name: 'app_contact')]
    public function addContact(Request $request, EntityManagerInterface $entityManager, Messagerie $messagerie): Response {
        $message = '';
        $mailStatus = '';

        // On instancie un objet contact
        $contact = new Contact;

        // On instancie un objet ContactType
        $form = $this->createForm(ContactType::class, $contact);
        $form->handleRequest($request); //Récupération des datas du formulaire

        //On vérifie si le formulaire a été soumis et correctement complété
        if ($form->isSubmitted() AND $form->isValid()) {

            // On nettoie les données de la requête et on set notre instance $contact (pas pour la date car le typage du champs dans le formulaire ne permet pas d'injection)
            $contact->setNom(Utils::cleanInputStatic($request->request->all('contact')['nom']));
            $contact->setPrenom(Utils::cleanInputStatic($request->request->all('contact')['prenom']));
            $contact->setMail(Utils::cleanInputStatic($request->request->all('contact')['mail']));
            $contact->setObjet(Utils::cleanInputStatic($request->request->all('contact')['objet']));
            $contact->setContenu(Utils::cleanInputStatic($request->request->all('contact')['contenu']));

            // On essaie de flush et persist les données
            try {
                $entityManager->persist($contact); //on fait persister les données
                $entityManager->flush(); //on envoie les données en BDD
                $message = 'Votre message a bien été envoyé'; //on implémente le message de réussite dans la variable $massage

                // On récupère les variables d'authentification du serveur mail pour utiliser la méthode sendMail() de la classe Messagerie
                $login = $this->getParameter('accountmail'); //getParamater() est une méthode de la classe native AbstractController
                $password = $this->getParameter('passmail');

                // Variable pour l'envoi du mail 
                $date = $contact->getDate()->format('d-m-Y'); //La date est un objet, on la formate donc en string
                $objet = $contact->getObjet();
                $content = '<p>Nom : <strong>'.mb_convert_encoding($contact->getNom(), 'ISO-8859-1', 'UTF-8').'</strong></p><hr>'. //On utilise .mb_conver_encoding() pour que kes accents et caractères spéciaux soit bien affichés dans le mail
                '<p>Prenom : <strong>'.mb_convert_encoding($contact->getPrenom(), 'ISO-8859-1', 'UTF-8').'</strong></p><hr>'.
                '<p>Mail : <strong>'.mb_convert_encoding($contact->getMail(), 'ISO-8859-1', 'UTF-8').'</strong></p><hr>'.
                '<p>Contenu : <strong>'.mb_convert_encoding($contact->getContenu(), 'ISO-8859-1', 'UTF-8').'</strong></p><hr>'.
                '<p>Date envoi : <strong>'.$date.'</strong></p><hr>';
                $destinataire = 'authie.maxime@orange.fr';

                // On récupère le statut via la fonction sendMail(variables en param) qui retourne un message
                $mailStatus = $messagerie->sendMail($login, $password,  $destinataire, $objet, $content,);

            //En cas d'échec du persist ou du flush, on renvoie un message d'erreur
            } catch (\Exception $error) {
                $message = $error;
            }
        }

        // On retourne le rendu pour l'interface TWIG
        return $this->render('contact/index.html.twig', [
            'message' => $message,
            'form' => $form->createView(),
            'status' => $mailStatus // pour renvoyé le message de confirmation/erreur de l'envoi de mail
        ]);
    }
}
