<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/")
 */
class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index", methods={"GET"})
     */
    public function index()
    {
        return $this->json([
            'program' => 'User Management System',
            'version' => '1.0',
            'status'  => 'success',
            'code'    => Response::HTTP_OK ,
            'message' => Response::$statusTexts[Response::HTTP_OK],
            'data'    => [
                'routes' => [
                    [
                        'method' => 'POST',
                        'path' => '/api/login_check',
                        'description' => 'Login to get api token'
                    ],
                ]
            ]
        ]);
    }
}
