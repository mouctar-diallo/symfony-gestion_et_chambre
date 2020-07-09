<?php
namespace App\Security;

use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use MercurySeries\FlashyBundle\FlashyNotifier;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\AuthenticatorInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\PasswordUpgradeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;

class LoginFormAuthenticator extends AbstractAuthenticator
{
    private $userRepository;
    private $urlGenerator;
    public function __construct(UserRepository $userRepository, UrlGeneratorInterface $urlGenerator,FlashyNotifier $flashy)
    {
        $this->userRepository = $userRepository;
        $this->urlGenerator = $urlGenerator;
        $this->flashy = $flashy;
        
    }
    /**
     * Does the authenticator support the given Request?
     *
     * If this returns false, the authenticator will be skipped.
     *
     * Returning null means authenticate() can be called lazily when accessing the token storage.
     */
    public function supports(Request $request): ?bool
    {
        return $request->attributes->get('_route') === 'login_page' && $request->isMethod('POST');

        //return true et appel function authenticate else return false
    }

    /**
     * Create a passport for the current request.
     *
     * The passport contains the user, credentials and any additional information
     * that has to be checked by the Symfony Security system. For example, a login
     * form authenticator will probably return a passport containing the user, the
     * presented password and the CSRF token value.
     *
     * You may throw any AuthenticationException in this method in case of error (e.g.
     * a UsernameNotFoundException when the user cannot be found).
     *
     * @throws AuthenticationException
     */
    public function authenticate(Request $request): PassportInterface
    {
        $user = $this->userRepository->findOneByEmail($request->get('email'));
        //gardons l'email dans la session
        $request->getSession()->set('email_user',$request->get('email'));
        if (!$user) {
            throw new UsernameNotFoundException();
            //si user n'exsite pas il genere exeption et appel la fonction onAuthenticationFailure
            //sinon la fonction  onAuthenticationSuccess est appelee
        }

        return new Passport($user, new PasswordCredentials($request->get('password')), [
            // $this->userRepository must implement PasswordUpgraderInterface
             //new PasswordUpgradeBadge($request->get('password'), $this->userRepository),
                        
            new CsrfTokenBadge('loginform', $request->get('crsf_token')),
            new RememberMeBadge
           
        ]);
    }

    /**
     * Called when authentication executed and was successful!
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the last page they visited.
     *
     * If you return null, the current request will continue, and the user
     * will be authenticated. This makes sense, for example, with an API.
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        //on supprime l'email dans la session sa ne sert a rien
        $request->getSession()->remove('email_user');
        $this->flashy->success("Bienvenue sur notre site internet");
        return new RedirectResponse($this->urlGenerator->generate('add_chambre'));
    }

    /**
     * Called when authentication executed, but failed (e.g. wrong username password).
     *
     * This should return the Response sent back to the user, like a
     * RedirectResponse to the login page or a 403 response.
     *
     * If you return null, the request will continue, but the user will
     * not be authenticated. This is probably not what you want to do.
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        //affiche message flash
        $this->flashy->error("login ou mot de passe incorrecte !");
        return new RedirectResponse($this->urlGenerator->generate('login_page'));
    }
}