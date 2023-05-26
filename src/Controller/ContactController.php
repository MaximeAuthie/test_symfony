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
use App\Service\Messagerie;
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
    public function addContact(Request $request, EntityManagerInterface $entityManager): Response {
        $message = '';

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

            // On flush et persist les données
            try {
                $entityManager->persist($contact); //on fait persister les données
                $entityManager->flush(); //on envoie les données en BDD
                $message = 'Votre message a bien été envoyé';
            } catch (\Exception $error) {
                $message = 'Une erreur s\'est produite lors de l\'envoi de votre message';
            }
        }

        // On retourne le rendu pour l'interface TWIG
        return $this->render('contact/index.html.twig', [
            'message' => $message,
            'form' => $form->createView()
        ]);
    }
}
