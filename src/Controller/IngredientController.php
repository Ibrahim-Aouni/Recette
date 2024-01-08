<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Form\IngredientType;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\Security\Core\Annotation\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


class IngredientController extends AbstractController
{
    /**
     * Undocumented function
     *
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    #[IsGranted('ROLE_USER')]
    #[Route('/ingredient', name: 'ingredient.index')]
    public function index( IngredientRepository $repository, PaginatorInterface $paginator, Request $request): Response
    {
        
        $ingredient= $paginator->paginate(
            $repository->findBy(['user'=>$this->getUser()]),
            $request->query->getInt('page', 1), /*page number*/
            10 /*limit per page*/
        );
       
        return $this->render('pages/ingredient/index.html.twig',[
            'ingredients' => $ingredient,
        ]);
    }
    /**
     * Undocumented function
     *
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[Route('/ingredient/creation','ingredient.new',methods:['GET','POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $manager) : Response
    {
        $ingredient = new Ingredient();
        $form =$this->createForm(IngredientType::class,$ingredient);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            
            $ingredient= $form->getData();
            $ingredient->setUser($this->getUser());
            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash('success',
             'Your changes were saved!');

             return $this->redirectToRoute('ingredient.index');

        }

        
        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->CreateView()  
        ]);

    }

     /**
     * This controller allow us to edit an ingredient
     *
     * @param Ingredient $ingredient
     * @param Request $request
     * @param EntityManagerInterface $manager
     * @return Response
     */
    #[IsGranted(
            attribute: new Expression('is_granted("ROLE_USER") and user === subject.getUser()'),
        subject: 'ingredient',
    )]
    #[Route('/ingredient/edit/{id}','ingredient.edit', methods:['GET', 'POST'])]
    public function edit(Ingredient $ingredient, Request $request, EntityManagerInterface $manager) : Response
    {
        $form =$this->createForm(IngredientType::class,$ingredient);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            
            $ingredient= $form->getData();
            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash('success',
             'Votre ingrédient a été modifié!');

             return $this->redirectToRoute('ingredient.index');

        }

        return $this->render('pages/ingredient/edit.html.twig', [
            'form'=> $form->createView()
        ]);
    }
    #[Route('ingredient/delete/{id}','ingredient.delete', methods:['GET'])]
    #[IsGranted('ROLE_USER')]
    public function delete(Ingredient $ingredient, EntityManagerInterface $manager) : Response
    {
        if (!$ingredient)
        {
            $this->addFlash('success',
             'Votre ingrédient n\'a pas été trouvé!');
        }
        $manager->remove($ingredient);
        $manager->flush();

        $this->addFlash('success',
             'Votre ingrédient a été supprimé!');


        return $this->redirectToRoute('ingredient.index');

    }
}
