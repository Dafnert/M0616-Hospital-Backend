<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\NurseRepository;


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
      public function findByName(string $name, NurseRepository $nurseRepository): JsonResponse
    {
        // Buscar coincidencias ignorando mayúsculas/minúsculas
        $results = $nurseRepository->createQueryBuilder('n')
            ->where('LOWER(n.nombre) = LOWER(:name)')
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();

        // Si no hay coincidencias
        if (empty($results)) {
            return $this->json([
                'success' => false,
                'message' => "No se encontró ningún enfermero con el nombre '{$name}'",
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }

        // Convertir entidades a arrays
        $data = array_map(function($nurse) {
            return [
                'id' => $nurse->getId(),
                'nombre' => $nurse->getNombre(),
                'apellido' => $nurse->getApellido(),
                'especialidad' => $nurse->getEspecialidad(),
                'usuario' => $nurse->getUsuario(),
                'contraseña' => $nurse->getContraseña(),
            ];
        }, $results);

        return $this->json([
            'success' => true,
            'data' => $data
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