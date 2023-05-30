<?php
namespace App\Service;
    use App\Repository\UserRepository;
    use App\Service\Utils;
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

    class ApiRegister{

        public function authentification(UserPasswordHasherInterface $passwordHasherInteface, UserRepository $userRepository, string $userEmail, string $userPassword) {
            
            // On nettoie les données issues de l'api
            $userEmail = Utils::cleanInputStatic($userEmail);
            $userPassword = Utils::cleanInputStatic($userPassword);

            // On récupère le compte utilisateur avec la méthode findOneBy de la classe UserRepository
            $user = $userRepository->findOneBy(['email' => $userEmail]);

            // On teste si le compte existe
            if ($user) {

                // On teste si le mot de passe est correct
                if ($passwordHasherInteface->isPasswordValid($user, $userPassword)) { //! ici, on passe en argument un objet user et le mdp en clair
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }

        public function genToken(string $mail, string $secretkey,UserRepository $repo) {

            //autolaod composer
            require_once('../vendor/autoload.php'); //Obligatoire

            //Variables pour le token
            $issuedAt   = new \DateTimeImmutable(); //Date de génération du token
            $expire     = $issuedAt->modify('+60 minutes')->getTimestamp(); //Date d'expiration du token
            $serverName = "your.domain.name"; //Domaine du site
            $username   = $repo->findOneBy(['email'=>$mail])->getNom(); //On récupère le nom via le 
            
            //Contenu du token
            $data = [
                'iat'  => $issuedAt->getTimestamp(),         // Timestamp génération du token
                'iss'  => $serverName,                       // Serveur
                'nbf'  => $issuedAt->getTimestamp(),         // Timestamp empécher date  (sécurité si quelqu'un récupère la clé de chiffrement)
                'exp'  => $expire,                           // Timestamp expiration du token
                'userName' => $username,                     // Nom utilisateur
            ];

            //implémenter la méthode statique encode de la classe JWT
                $token = JWT::encode($data, $secretkey, 'HS512');

            //Retourner le token
                return $token;
        }

        public function verifyToken(string $token, string $key) {
            //autolaod composer
            require_once('../vendor/autoload.php'); //Obligatoire

            try {
                //On décode le token (on vérifie s'il est valide et la méthode retourne une exception avec un message si quelque chose ne va pas dans  son contenu )
                $decodeToken = JWT::decode($token, new Key($key, 'HS512'));

                //On retourne true si il a pu décoder le token (s'il n'y arrive pas, il renvoie une exception sans passer par cette étape)
                return true;

            } catch (\Throwable $error) {
                return $error->getMessage();
            }
        }
    }
?>