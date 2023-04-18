<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager; //permet d'écrire ou interagir avec la BDD
use Faker; //Ajouté manuellement 
use App\Entity\Category; //Ajouté manuellement
use App\Entity\Article;
use App\Entity\User;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tabCat = [];
        $userTab = [];
        $faker = Faker\Factory::create('fr_FR'); //Ajouté manuellement
        
        for ($i =0 ; $i<10 ; $i++) {
            $cat = new Category();
            $cat->setNom($faker->jobTitle());
            $manager->persist($cat); //permet de checker si l'objet existe en BDD et de l'insérer en BD
            $tabCat[]=$cat; //méthode pour push en php (existe aussi array push)
        }
        for ($i=0 ; $i<5 ; $i++) {
            $user= new User();
            $user->setEmail($faker->email());
            $user->setRoles(['ROLE_USER', 'ROLE_ADMIN']);
            $user->setPassword(password_hash('1234', PASSWORD_DEFAULT));
            $user->setNom($faker->lastName());
            $user->setPrenom($faker->firstName());
            $manager->persist($user);
            $userTab[]=$user; // on stocke les objets User dans un tableau pour les ajouter ensuite à Article (pour la relation)
        }

        for ($i=0 ; $i<10 ; $i++) {
            $article = new Article();
            $article->setTitre($faker->words(3, true));
            $article->setContenu($faker->sentence(5));
            $article->setDate(new \DateTimeImmutable($faker->date('Y-m-d'))); //Ici on met un \ pour expliquer à Symfony qu'elle se situe à l'extérieur de l'entité actuelle
            $article->setUser($userTab[$faker->numberBetween(0, 4)]); //vient setter un objet du tableau userTab de manière aléatoire
            $article->addCategory($tabCat[$faker->numberBetween(0, 2)]);
            $article->addCategory($tabCat[$faker->numberBetween(3, 5)]);
            $article->addCategory($tabCat[$faker->numberBetween(6, 9)]);
            $manager->persist($article);
        }

        $manager->flush(); //Synchronise et vide l'objet
    }
}
