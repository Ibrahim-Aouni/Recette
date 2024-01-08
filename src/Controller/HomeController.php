<?php

namespace App\Controller;

use App\Repository\RecetteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/','home',methods:['GET'])]
    public function index(RecetteRepository $repository): Response
    {
        return $this->render('home.html.twig', [
            'recettes' => $repository->findPublicRecette(3)
        ]);

    }
}