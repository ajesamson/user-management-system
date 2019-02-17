<?php

namespace App\Controller;

use App\Services\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        $data = [
            'routes' => [
                [
                    'method' => 'POST',
                    'path' => '/api/login_check',
                    'description' => 'Login to get api token'
                ],
            ]
        ];

        return $this->json(ResponseService::successResponse($data));
    }
}
