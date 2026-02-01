<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\NurseRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Nurse;


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

    
    'img' => '/uploads/nurses/images.jpg',
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
        // Obtain data request
        $data = json_decode($request->getContent(), true);

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

    #[Route('/', name: 'app_nurse_create', methods: ['POST'])]
    public function createNurse(Request $request, NurseRepository $nurseRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (
            !isset($data['name']) ||
            !isset($data['surname']) ||
            !isset($data['age']) ||
            !isset($data['speciality']) ||
            !isset($data['username']) ||
            !isset($data['password'])
        ) {
            return $this->json([
                'success' => false,
                'message' => 'Missing required fields'
            ], Response::HTTP_BAD_REQUEST);
        }

        $nurse = new Nurse();
        $nurse->setName($data['name']);
        $nurse->setSurname($data['surname']);
        $nurse->setAge($data['age']);
        $nurse->setSpeciality($data['speciality']);
        $nurse->setUsername($data['username']);
        $nurse->setPassword($data['password']);

        $nurseRepository->save($nurse, true);

        return $this->json([
            'success' => true,
            'message' => "Nurse '{$nurse->getName()}' created successfully",
            'data' => [
                'id' => $nurse->getId(),
                'name' => $nurse->getName(),
                'surname' => $nurse->getSurname(),
                'age' => $nurse->getAge(),
                'speciality' => $nurse->getSpeciality(),
                'username' => $nurse->getUsername(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_nurse_delete', methods: ['DELETE'])]
    public function deleteById(int $id, NurseRepository $nurseRepository): JsonResponse
    {
        $nurse = $nurseRepository->find($id);

        if (!$nurse) {
            return $this->json([
                'success' => false,
                'message' => "No nurse was found with the ID'{$id}'"
            ], Response::HTTP_NOT_FOUND);
        }

        $entityManager = $nurseRepository->getEntityManager();
        $entityManager->remove($nurse);
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => "Nurse '{$nurse->getName()}'Deleted successfully"
        ], Response::HTTP_OK);
    }
    #[Route('/{id}', name: 'nurse_searchById', methods: ['GET'])]
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
                        'age' => $nurse->getAge(),
                        'username' => $nurse->getUsername(),
                        'password' => $nurse->getPassword(),
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
    }

    #[Route(path: '/{id}', name: 'app_nurse_update', methods: ['PUT'])]
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
        $data = json_decode($request->getContent(), true);

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