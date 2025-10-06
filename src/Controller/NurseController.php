<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/nurse')]
final class NurseController extends AbstractController
{
    #[Route('/login', name: 'app_nurse', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        // Obtener datos de la request (JSON enviado desde Postman)
        $data = json_decode($request->getContent(), true);
        $nursesFile = $this->getParameter('kernel.project_dir') . '/public/nurses.json';
        $nursesData = json_decode(file_get_contents($nursesFile), true);
        $nurses = $nursesData ?? []; 
        
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
        // If the nurse exists, show all the nurse data.
        foreach ($nurses as $nurse) {
            if ($nurse['username'] === $username && $nurse['password'] === $password) {
                return $this->json(
                    [
                        'success' => true,
                        'message' => 'Success',
                        'nurse' => [
                            'name' => $nurse['name'],
                            'email' => $nurse['email'],
                        ]
                    ],
                    Response::HTTP_OK
                );
            }
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