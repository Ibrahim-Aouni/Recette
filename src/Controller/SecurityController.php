<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    #[Route('/connexion', name:'security.login', methods: ['GET','POST'])]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
       
        return $this->render('pages/security/login.html.twig',[
            'last_username' => $authenticationUtils->getLastUsername(),
            'error' => $authenticationUtils->getLastAuthenticationError()
        ]);
        
    }
    #[Route('/logout', name:"security.logout")]
    public function logoutAction()
    {

    }
    #[Route('/inscription ', name:"security.registration", methods: ['GET', 'POST'])]
    public function registration(Request $request, EntityManagerInterface $manager ) :Response
    {   
        $user= new User();
        $user->setRoles(['ROLE_USER']);
        $form= $this->createForm(RegistrationType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();
            $recaptchaToken = $request->request->get('recaptchaToken');
            

            $manager->persist($user);
            $manager->flush();

            $this->addFlash(
                'success',
                'Vous êtes bien inscrit !'
            );

            return $this->redirectToRoute('recette.index');
        }
        

        return $this->render('pages/security/registration.html.twig',[
            'form' => $form->createView(),

        ]);
    }
}
