<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Repository\NurseRepository;


#[Route(path: '/nurse')]
final class NurseController extends AbstractController
{
    #[Route(path: '/index', name: 'app_nurse_index')]
    public function getAll(): JsonResponse
    {
        $jsonPath = $this->getParameter('kernel.project_dir') . '/public/nurses.json';
        $json_nurse = file_get_contents(filename: 'nurses.json');
        $json_nurse = json_decode(json: $json_nurse, associative: true);

        return new JsonResponse(data: $json_nurse, status: Response::HTTP_OK);
    }

    #[Route(path: '/name/{name}', name: 'app_nurse_findbyname')]
    public function findbyname(string $name): JsonResponse
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
            ], Response::HTTP_NOT_FOUND);
        }

        // Devolver coincidencia(s)
        return $this->json([
            'success' => true,
            'name' => $name,
            'data' => $results

        ], Response::HTTP_OK);
    }
    #[Route('/login', name: 'app_nurse_login', methods: ['POST'])]
    public function login(Request $request, NurseRepository $nurseRepository): JsonResponse
    {
        // Obtener datos de la request (JSON enviado desde Postman)
        $data = json_decode($request->getContent(), true);
        // $nursesFile = $this->getParameter('kernel.project_dir') . '/public/nurses.json';
        // $nursesData = json_decode(file_get_contents($nursesFile), true);
        // $nurses = $nursesData ?? []; 

        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        // If the nurse dont put username or password taht are required
        if (empty($username) || empty($password)) {
            return $this->json(
                [
                    'success' => false,
                    'message' => 'Username and password are required',
                ],
                Response::HTTP_BAD_REQUEST
            );
        }
        //Busca en la base de datos el username
        // primero verificamos si el username exite o no
        $nurse = $nurseRepository->findOneBy(['username' => $username]);
        // If the nurse exists, show all the nurse data.
        if ($nurse && $nurse->getPassword() === $password) {
            return $this->json(
                [
                    'success' => true,
                    'message' => 'Success',
                    'nurse' => [
                        'id' => $nurse->getId(),
                        'name' => $nurse->getName(),
                        'surname' => $nurse->getSurname(),
                        'username' => $nurse->getUsername(),
                        'speciality' => $nurse->getSpeciality(),
                    ]
                ],
                Response::HTTP_OK
            );
        }
        // if the nurse not exixts, show a message
        return $this->json(
            [
                'success' => false,
                'message' => 'Invalid credentials',
            ],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
