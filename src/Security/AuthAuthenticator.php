<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use App\Repository\UserRepository;
use App\Entity\User;

class AuthAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    private $repo;
    public const LOGIN_ROUTE = 'app_login';

    public function __construct(private UrlGeneratorInterface $urlGenerator, UserRepository $userRepository) //! On passe User repo dans le constructeur parce que incompatible avec les paramètres d'une méthode de cette classe
    {
        $this->repo = $userRepository; //! Les classe UserRepository et AuthAuthentification sont incompatibles => on associe une instance de UserRepository dans la varaible $repo de l'instance en cours
        
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email', '');

        $request->getSession()->set(Security::LAST_USERNAME, $email);
        //! Voir si on ne peut pas mettre les vérifications sur l'activation ici
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->request->get('password', '')),
            [
                new CsrfTokenBadge('authenticate', $request->request->get('_csrf_token')),
            ]
        );
    }

    // Méthode qui s'applique si l'authentification est réussie.
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // On récupère le mail de l'utilisateur 
        $email = $request->request->get('email', '');

        // On récupère l'utilsateur depuis la BDD
        $user = $this->repo->findOneBy(['email'=> $email]);
        // dd($user, $user->getId());
        // On vérifie si l'utilisateur est activé
        if ($user->isActivate()) {
            // if ($targetPath = $this->getTargetPath($request->getSession(), $firewallName)) { 
            //     return new RedirectResponse($targetPath);
            // }
            return new RedirectResponse($this->urlGenerator->generate('app_home')); //! On ajoute cette ligne pour regiriger vers la page d'accueil
        } else {
            // Si le compte n'est pas activié on renvoie vers la méthode qui renvoie un mail d'activation
            return new RedirectResponse($this->urlGenerator->generate('app_send_activate', ['id' =>$user->getId()]));
        }

        



        // For example:
        // return new RedirectResponse($this->urlGenerator->generate('some_route'));
        // throw new \Exception('TODO: provide a valid redirect inside '.__FILE__); //! On commente cette ligne
        
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
