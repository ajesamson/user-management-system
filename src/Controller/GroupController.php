<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\Group;
use App\Traits\ResponseTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/groups")
 */
class GroupController extends AbstractController
{
    /**
     * @var string
     */
    private static $invalidGroup = 'Invalid user group id';

    /**
     * @var string
     */
    private static $usersExist = 'Group has existing user';

    /**
     * @Route("/", name="groups_index", methods={"GET"})
     */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $groups = $entityManager->getRepository(Group::class)->findAll();

        return $this->json(ResponseTrait::successResponse($groups));
    }

    /**
     * @Route("/add", name="groups_add", methods={"POST"})
     */
    public function add(Request $request, ValidatorInterface $validator)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $group = $serializer->deserialize($request->getContent(), Group::class, 'json');
        $validator->validate($group);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($group);
        $entityManager->flush();

        return $this->json(ResponseTrait::successResponse($group, Response::HTTP_CREATED));
    }

    /**
     * @Route("/{id}", name="groups_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $group = $entityManager->getRepository(Group::class)->find($id);

        if (!$group) {
            return $this->json(ResponseTrait::errorResponse(self::$invalidGroup));
        }

        if (!$group->getUsers()->isEmpty()) {
            return $this->json(ResponseTrait::errorResponse(self::$usersExist));
        }

        $entityManager->remove($group);
        $entityManager->flush();

        return $this->json(ResponseTrait::successResponse(null, Response::HTTP_NO_CONTENT));
    }
}
