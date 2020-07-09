<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\FormLoginType;
use App\Form\UserRegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class SecurityController extends AbstractController
{
    private $entityManager;
    public function __construct(EntityManagerInterface $em)
    {
        $this->entityManager = $em;
    }
    /**
     * @Route("/register", name="register_page",methods={"POST","GET"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $encodeMotDePasse) : Response
    {
        $form = $this->createForm(UserRegisterFormType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $password = $form->get('plainPassword')->getData();

            $user->setPassword($encodeMotDePasse->encodePassword($user,$password));

            $this->entityManager->persist($user);
            $this->entityManager->flush();

            $this->addFlash('success', 'User created successfully !');
            return $this->redirectToRoute('home_page');
        }

        return $this->render('security/register.html.twig', [
            'registration_form' => $form->createView()
        ]);
    }
    /**
     * @Route("/login", name="login_page",methods={"GET","POST"})
     */
    public function login() : Response
    {
        return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/logout", name="logout",methods={"GET"})
     */
    public function logout()
    {
        throw new \LogicException('cette methode est gerer par la cle logout de notre firewalls');
    }
}
