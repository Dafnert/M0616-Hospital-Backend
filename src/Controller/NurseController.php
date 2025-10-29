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

    public function getAll(NurseRepository $nurseRepository): JsonResponse
    {
        // Obtener todas las enfermeras desde la base de datos
        $nurses = $nurseRepository->findAll();

        // Verifica si no hay enfermeras en la base de datos
        if (!$nurses) {
            return new JsonResponse(['error' => 'No nurses found'], Response::HTTP_NOT_FOUND);
        }

        $data = array_map(function ($nurse) {
            return [
                 'id' => $nurse->getId(),
                'name' => $nurse->getName(),
                'surname' => $nurse->getSurname(),
                'age' => $nurse->getAge(),
                'speciality' => $nurse->getSpeciality(),
                'username' => $nurse->getUsername(),
                'password' => $nurse->getPassword(),
            ];
        }, $nurses);

        return new JsonResponse($data, Response::HTTP_OK);
    }

    
    #[Route(path: '/name/{name}', name: 'app_nurse_findbyname')]
    public function findByName(string $name, NurseRepository $nurseRepository): JsonResponse
    {
        $results = $nurseRepository->createQueryBuilder('n')
            ->where('LOWER(n.name) = LOWER(:name)')
            ->setParameter('name', $name)
            ->getQuery()
            ->getResult();

        if (empty($results)) {
            return $this->json([
                'success' => false,
                'message' => "No nurse found with the given name '{$name}'",
                'data' => []
            ], Response::HTTP_NOT_FOUND);
        }
        $data = array_map(function ($nurse) {
            return [
                'id' => $nurse->getId(),
                'name' => $nurse->getName(),
                'surname' => $nurse->getSurname(),
                'age' => $nurse->getAge(),
                'speciality' => $nurse->getSpeciality(),
                'username' => $nurse->getUsername(),
                'password' => $nurse->getPassword(),
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



    #CREACION DEL CRUD
    #[Route('/new', name: 'app_nurse_create', methods: ['POST'])]

    public function createNurse(Request $request, NurseRepository $nurseRepository): JsonResponse
    {
        // Obtener datos enviados en formato JSON
        $data = json_decode($request->getContent(), true);

        // Validar que los campos requeridos estén presentes
        $requiredFields = ['name', 'surname', 'age', 'speciality', 'username', 'password'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->json(
                    [
                        'success' => false,
                        'message' => "This is '{$field}' is required"
                    ],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }

        // Verificar si ya existe un usuario con ese username
        $existingNurse = $nurseRepository->findOneBy(['username' => $data['username']]);
        if ($existingNurse) {
            return $this->json(
                [
                    'success' => false,
                    'message' => 'The username is already in use.'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        // Crear nuevo objeto Nurse (asumiendo que existe la entidad App\Entity\Nurse)
        $nurse = new \App\Entity\Nurse();
        $nurse->setName($data['name']);
        $nurse->setSurname($data['surname']);
        $nurse->setAge($data['age']);
        $nurse->setSpeciality($data['speciality']);
        $nurse->setUsername($data['username']);
        $nurse->setPassword($data['password']);

        // Guardar en base de datos 
        //llamamos al metodo save() que esta en el repopositorNurse
        $nurseRepository->save($nurse, true); // El segundo parámetro true hace el flush

        //Aqui se devuelve la respuesta 
        return $this->json(
            [
                'success' => true,
                'message' => 'Nurse created successfully.',
                'data' => [
                    'id' => $nurse->getId(),
                    'name' => $nurse->getName(),
                    'surname' => $nurse->getSurname(),
                    'age' => $nurse->getAge(),
                    'speciality' => $nurse->getSpeciality(),
                    'username' => $nurse->getUsername()
                ]
            ],
            Response::HTTP_CREATED // 201 OK
        );
    }

}