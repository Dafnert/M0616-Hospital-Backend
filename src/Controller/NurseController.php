<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\NurseRepository;
use Doctrine\ORM\EntityManagerInterface;



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
                Response::HTTP_UNAUTHORIZED
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
            Response::HTTP_NOT_FOUND
        );
    }
    #[Route('/{id}', name: 'nurse_searchById')]
    public function readById(int $id,  NurseRepository $nurseRepository): JsonResponse
    {
        //Search nurse by ID
        $nurse = $nurseRepository->find($id);
        //If the nurse exist, show us data
        if ($nurse) {
            return $this->json(
                [
                    'success' => true,
                    'message' => 'Nurse found',
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
        return $this->json(
            [
                'success' => false,
                'message' => 'Nurse not found',
            ],
            Response::HTTP_NOT_FOUND
        );
<<<<<<< HEAD
    }

    #[Route(path: '/{id}', name: 'app_nurse_update', methods: ['PUT'])]



    #[Route('/{id}', name: 'api_update_auxiliar', methods: ['PUT'])]
    public function updateNurse(
        int $id,
        Request $request,
        NurseRepository $nurseRepository,
        EntityManagerInterface $entityManager,
    ): JsonResponse {

        // Buscar la enfermera por ID
        $nurse = $nurseRepository->find($id);

        if (!$nurse) {
            return new JsonResponse(['error' => 'Nurse not found'], Response::HTTP_NOT_FOUND);
        }

        // Decodificar los datos JSON del cuerpo de la petición
        $data = json_decode($request->getContent(), true);

        // Actualizar los campos si se proporcionan
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
        if (!empty($data['password'])) {
            $nurse->setPassword($data['password']);
        }

        // Save the changes
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Nurse updated successfully',
            'nurse' => [
                'id' => $nurse->getId(),
                'name' => $nurse->getName(),
                'surname' => $nurse->getSurname(),
                'age' => $nurse->getAge(),
                'speciality' => $nurse->getSpeciality(),
                'username' => $nurse->getUsername(),
            ]
        ], Response::HTTP_OK);
    }
}
=======
 }

}
>>>>>>> 2798506c1a84124b8ac1314b6e4278d90d45b181
