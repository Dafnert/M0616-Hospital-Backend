<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Response;

#[Route(path: '/nurse')]
final class NurseController extends AbstractController
{
    // Get all nurses
    //luego tenemos que ponernos de acuerdo para poner el nombre de la url en equipo

    #[Route(path: '/index', name: 'app_nurse')]
    public function getAll(): JsonResponse
    {
        // __DIR__ = carpeta actual de este controlador esta en public
        //es decir esto construye una ruta en symfony para poder llegar al documento nurse.json que esta en la carpeta PUBLIC.
        //kernel.project_dir es un parametro predefinido en Symfony que represneta la ruta del proyecto
        $jsonPath = $this->getParameter('kernel.project_dir') . '/public/nurses.json';
        $json_nurse = file_get_contents(filename: 'nurseS.json');
        $json_nurse = json_decode(json: $json_nurse, associative: true);
        // este lo que hace es convertir informcacion en formato json y guardarlos?
        //return data as a list of nurse in json format
        return new JsonResponse(data: $json_nurse, status: Response::HTTP_OK);
    }  

}
