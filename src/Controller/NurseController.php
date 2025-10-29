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
                'message' => "No se encontró ningún enfermero con el nombre '{$name}'",
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

#[Route(path: '/{id}', name: 'app_nurse_update', methods: ['PUT'])]

public function updateNurse(
    int $id,
    Request $request,
    NurseRepository $nurseRepository,
    \Doctrine\ORM\EntityManagerInterface $entityManager
): JsonResponse {
    // Buscar enfermera por ID
    $nurse = $nurseRepository->find($id);

    if (!$nurse) {
        return $this->json([
            'success' => false,
            'message' => "No nurse with the ID was found. {$id}"
        ], Response::HTTP_NOT_FOUND);
    }

    // Obtener datos del cuerpo de la solicitud
    $data = json_decode($request->getContent(), true);

    // Validar que se haya enviado algún campo para actualizar
    if (!$data || !is_array($data)) {
        return $this->json([
            'success' => false,
            'message' => 'No valid data was sent to update.'
        ], Response::HTTP_BAD_REQUEST);
    }

    // Actualizar solo los campos enviados (manejo parcial)
    if (isset($data['name'])) {
        $nurse->setName($data['name']);
    }
    if (isset($data['surname'])) {
        $nurse->setSurname($data['surname']);
    }
    if (isset($data['age'])) {
        $nurse->setAge($data['age']);
    }
    if (isset($data['speciality'])) {
        $nurse->setSpeciality($data['speciality']);
    }
    if (isset($data['username'])) {
        $nurse->setUsername($data['username']);
    }
    if (isset($data['password'])) {
        $nurse->setPassword($data['password']);
    }

    // Guardar cambios en la base de datos
    $entityManager->persist($nurse);
    $entityManager->flush();

    return $this->json([
        'success' => true,
        'message' => 'Nurse correctly updated.',
        'data' => [
            'id' => $nurse->getId(),
            'name' => $nurse->getName(),
            'surname' => $nurse->getSurname(),
            'age' => $nurse->getAge(),
            'speciality' => $nurse->getSpeciality(),
            'username' => $nurse->getUsername(),
            'password' => $nurse->getPassword(),
        ]
    ], Response::HTTP_OK);
}


}