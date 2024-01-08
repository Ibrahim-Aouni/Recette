<?php

namespace App\Controller;
use App\Entity\User;

use App\Form\UserPasswordType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class UserController extends AbstractController
{
    #[IsGranted(
        attribute: new Expression('is_granted("ROLE_USER") and user === subject'),
    subject: 'user',
   )]
    #[Route('/utilisateur/edit/{id}', name: 'user.edit', methods: ['GET', 'POST'])]
    public function edit(User $user): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute('security.login');
        }
        if($this->getUser() !== $user)
        {
            return $this->redirectToRoute('recette.index');
        }
        $form= $this->createForm(UserType::class, $user);
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    #[IsGranted(
        attribute: new Expression('is_granted("ROLE_USER") and user === subject'),
    subject: 'user',
   )]
    #[Route('/utilisateur/edit_password/{id}',name:'user.edit.password',methods:['GET','POST'])]
    public function editPassword(
        Request $request,
        EntityManagerInterface $manager,
        UserPasswordHasherInterface $hasher
    ): Response {

        $user = new User();
        $form = $this->createForm(UserPasswordType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($hasher->isPasswordValid($user, $form->getData()['plainPassword'])) {
                $user->setUpdatedAt(new \DateTimeImmutable());
                $user->setPlainPassword(
                    $form->getData()['newPassword']
                );

                $this->addFlash(
                    'success',
                    'Le mot de passe a été modifié.'
                );

                $manager->persist($user);
                $manager->flush();

                return $this->redirectToRoute('recette.index');
            } else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }
        }

        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView()
        ]);
    }

}
