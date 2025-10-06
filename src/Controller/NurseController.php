<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/nurse')]
final class NurseController extends AbstractController
{
    #[Route(path: '/name/{name}', name: 'name_nurse')]
    public function Findbyname(string $name): JsonResponse
    {
        // Ruta al JSON en /public
        $jsonPath = $this->getParameter('kernel.project_dir') . '/public/nurses.json'; 
        $nurses = [];

        if (file_exists($jsonPath)) {
            $jsonContent = file_get_contents($jsonPath);
            $nurses = json_decode($jsonContent, true);
        }

        // Filtrar solo coincidencias exactas
        $results = array_filter($nurses, fn($nurse) => strcasecmp($nurse['name'], $name) === 0);

        // Reindexar resultados
        $results = array_values($results);

        // Si no hay coincidencias
        if (empty($results)) {
            return $this->json([
                'success' => false,
                'message' => "Not Found {$name}",
                'data' => []
            ], 404);
        }

        // Devolver coincidencia(s)
        return $this->json([
            'success' => true,
            'name' => $name,
            'data' => $results
        ]);
    }
}
