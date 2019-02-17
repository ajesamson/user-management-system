<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\Group;
use App\Services\ResponseService;
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
    private const INVALID_GROUP = 'Invalid user group id';

    private const GROUP_ACTIVE = 'Group has existing user';

    private const GROUP_DELETED = 'Group deleted successfully';

    /**
     * @Route("/", name="groups_index", methods={"GET"})
     * Lists all groups
     */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $groups = $entityManager->getRepository(Group::class)->findAll();

        return $this->json(
            ResponseService::getSuccessResponse($groups, null)
        );
    }

    /**
     * @Route("/add", name="groups_add", methods={"POST"})
     * Adds a new group
     * @param Request $request
     * @param ValidatorInterface $validator
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function add(Request $request, ValidatorInterface $validator)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $requestBody = $request->getContent();

        if (!$requestBody) {
            return $this->json(
                ResponseService::getErrorResponse(self::INVALID_GROUP),
                Response::HTTP_BAD_REQUEST
            );
        }

        $group = $serializer->deserialize(
            $request->getContent(),
            Group::class,
            'json'
        );
        $validator->validate($group);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($group);
        $entityManager->flush();

        return $this->json(
            ResponseService::getSuccessResponse($group, Response::HTTP_CREATED),
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route(
     *     "/{id}",
     *     name="groups_delete",
     *     methods={"DELETE"},
     *     requirements={"id":"\d+"}
     * )
     * Deletes a group with the specified route id parameter
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $group = $entityManager->getRepository(Group::class)->find($id);

        if (!$group) {
            return $this->json(
                ResponseService::getErrorResponse(self::INVALID_GROUP),
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!$group->getUsers()->isEmpty()) {
            return $this->json(
                ResponseService::getErrorResponse(self::GROUP_ACTIVE),
                Response::HTTP_BAD_REQUEST
            );
        }

        $entityManager->remove($group);
        $entityManager->flush();

        return $this->json(
            ResponseService::getSuccessResponse(
                null,
                self::GROUP_DELETED,
                Response::HTTP_NO_CONTENT
            ),
            Response::HTTP_NO_CONTENT
        );
    }
}
