<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Recette;
use App\Repository\IngredientRepository;
use App\Repository\RecetteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route('/api', name: 'api_')]
class ApiController extends AbstractController
{
    #[Route('/recettes  ', name: 'endpoint', methods: ['GET'])]
    public function recettes(RecetteRepository $repository): JsonResponse
    {
        $data = $repository->findAll();

        // Transformer les données en un format utilisable pour la réponse JSON
        $formattedData = []; // Structurez vos données ici

        foreach ($data as $item) {
            // Exemple de structure pour chaque élément de $data (à adapter à vos besoins)
            $formattedData[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'price' => $item->getPrice(),
                'description' => $item->getDescription(),
                'difficulty' => $item->getDifficulty(),
                // Ajoutez d'autres champs si nécessaire
            ];
        }

        return $this->json($formattedData);
    }
    #[Route('/recette/{id}', name: 'endpoint_single', methods: ['GET'])]
    public function ingredients(Recette $recette): JsonResponse
    {
        // Si vous avez déjà l'entité $recette, vous pouvez le formater directement
        $formattedData = [
            'id' => $recette->getId(),
            'name' => $recette->getName(),
            'price' => $recette->getPrice(),
            'description' => $recette->getDescription(),

        ];
    
        return $this->json($formattedData);
    }
    #[Route('/ingredients', name:'ingredients', methods: ['GET', 'POST'])]
    public function ingredient(IngredientRepository $ingredient): JsonResponse
    {
        $data = $ingredient->findAll();
        $formattedData = [];
        foreach ($data as $item) {
            // Exemple de structure pour chaque élément de $data (à adapter à vos besoins)
            $formattedData[] = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'price' => $item->getPrice()
                // Ajoutez d'autres champs si nécessaire
            ];
        }
        return $this->json($formattedData);
    }
}
