<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Mark;
use App\Form\MarkType;
use App\Entity\Recette;
use App\Form\RecetteType;
use Doctrine\ORM\EntityManager;
use App\Repository\MarkRepository;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecetteController extends AbstractController
{

    /**
     * Undocumented function
     *
     * @param PaginatorInterface $paginator
     * @param RecetteRepository $repository
     * @param Request $request
     * @return Response
     */
    #[Route('/recette', name: 'recette.index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]

    public function index(PaginatorInterface $paginator, RecetteRepository $repository, Request $request): Response
    {
        $recettes = $paginator->paginate(
        $repository->findBy(['user'=> $this->getUser()]), 
        $request->query ->getInt('page',1),
        10);
        return $this->render('pages/recette/index.html.twig', [
            'recettes' => $recettes,
        ]);
    }
     #[Route('/recette/public','recette.community',methods:['GET'])]
     public function indexPublic(PaginatorInterface $paginator, RecetteRepository $repository, Request $request)
     {
         $queryBuilder = $repository->createQueryBuilder('r')
             ->where('r.isPublic = 1')
             ->orderBy('r.createdAt', 'DESC');
     
         $recettes = $paginator->paginate(
             $queryBuilder->getQuery(), // Utilisation du QueryBuilder avec le Paginator
             $request->query->getInt('page', 1),
             10
         );
     
         // Debug pour vérifier les résultats avant le rendu de la vue
         dump($recettes); // Utilisation de dump pour afficher les données récupérées
     
         return $this->render('pages/recette/community.html.twig', [
             'recettes' => $recettes
         ]);
     }
     
    #[Route('/recette/new', name: 'recette.new')]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recette= new Recette();
        $form= $this->createForm(RecetteType::class, $recette);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $recette = $form->getData();
            $recette->setUser($this->getUser());
            $manager->persist($recette);
            $manager->flush();

            return $this->redirectToRoute('recette.index');
        }
        return $this->render('pages/recette/new.html.twig',[
            'form' => $form->createView(),
        ]
    );
    }
    #[IsGranted(
        attribute: new Expression('is_granted("ROLE_USER") and user === subject.getUser()'),
    subject: 'recette',
    )]
    #[Route('/recette/edit/{id}', 'recette.edit', methods: ['GET', 'POST'])]
    public function edit(
        Recette $recette,
        Request $request,
        EntityManagerInterface $manager
    ): Response {
        $form = $this->createForm(RecetteType::class, $recette);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $recette = $form->getData();

            $manager->persist($recette);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été modifié avec succès !'
            );

            return $this->redirectToRoute('recette.index');
        }
        
        return $this->render('pages/recette/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }
   
    #[Route('recette/delete/{id}','recette.delete', methods:['GET'])]
    public function delete(Recette $recette, EntityManagerInterface $manager) : Response
    {
        if (!$recette)
        {
            $this->addFlash('success',
             'Votre ingrédient n\'a pas été trouvé!');
        }
        $manager->remove($recette);
        $manager->flush();

        $this->addFlash('success',
             'Votre ingrédient a été supprimé!');


        return $this->redirectToRoute('recette.index');

    }
    
    #[Route('recette/vegan','recette.vegan', methods:['GET'])]
    public function vegan( RecetteRepository $recetteRepository) : Response
    {
        $queryBuilder= $recetteRepository->createQueryBuilder('recette');

        $query = $queryBuilder
        ->select('recette')
        ->leftJoin('recette.ingredients', 'ingredient')
        ->andWhere('ingredient.type != :ingredientType OR ingredient.type IS NULL')
        ->setParameter('ingredientType', 'viande')
        ->orderBy('recette.id', 'ASC')
        ->getQuery();

        $results= $query->getResult();
       ;

        return $this->render('pages/recette/vegan.html.twig',[
            'recettes' => $results
        ]);

    }
    #[Route('recette/poulet','recette.poulet', methods:['GET'])]
    public function poulet( RecetteRepository $recetteRepository) : Response
    {
        $queryBuilder= $recetteRepository->createQueryBuilder('recette');

        $query = $queryBuilder
        ->select('recette')
        ->leftJoin('recette.ingredients', 'ingredient')
        ->andWhere('ingredient.type = :ingredientType ')
        ->setParameter('ingredientType', 'viande')
        ->groupBy('recette.id') 
        
        ->getQuery();

        $results= $query->getResult();
       ;

        return $this->render('pages/recette/poulet.html.twig',[
            'recettes' => $results
        ]);

    }

    #[Route('/recette/{id}','recette.show',methods:['GET','POST'])]
    public function show(Recette $recette, Request $request, MarkRepository $markRepository,
    EntityManagerInterface $manager
){

        $mark = new Mark();
        $form= $this->createForm(MarkType::class, $mark);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $mark->setUser($this->getUser())
                ->setRecette($recette);
                $existingMark = $markRepository->findOneBy([
                    'user' => $this->getUser(),
                    'recette' => $recette
                ]);
    
                if (!$existingMark) {
                    $manager->persist($mark);
                } else {
                    $existingMark->setMark(
                        $form->getData()->getMark()
                    );
                }
    
                $manager->flush();
    
                $this->addFlash(
                    'success',
                    'Votre note a bien été prise en compte.'
                );
    
                return $this->redirectToRoute('recette.show', ['id' => $recette->getId()]);
            }

            return $this->render('pages/recette/show.html.twig', [
                'recette' => $recette,
                'form' => $form->createView()
            ]);
        
    
    }
   
}
