<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\Group;
use App\Entity\User;
use App\Services\ResponseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/users")
 */
class UserController extends AbstractController
{
    private const INVALID_GROUP = 'Invalid user group name';

    private const INVALID_USER = 'Invalid user id';

    private const USER_CREATE_SUCCESS = 'New user added successfully';

    private const USER_DELETED_SUCCESS = 'Deleted user record successfully';

    private const ADD_GROUP_SUCCESS = 'User successfully added to group';

    private const DELETE_GROUP_SUCCESS = 'User successfully removed from group';

    /**
     * @Route("/", name="users_index", methods={"GET"})
     * Lists all users
     */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findAll();

        return $this->json(ResponseService::getSuccessResponse($user, null));
    }

    /**
     * @Route("/add", name="users_add", methods={"POST"})
     * Adds a new unique user
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
                ResponseService::getErrorResponse(self::INVALID_USER),
                Response::HTTP_BAD_REQUEST
            );
        }

        $users = $serializer->deserialize($requestBody, User::class, 'json');
        $validator->validate($users);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($users);
        $entityManager->flush();

        return $this->json(
            ResponseService::getSuccessResponse(
                $users,
                self::USER_CREATE_SUCCESS,
                Response::HTTP_CREATED
            ),
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route(
     *     "/{id}",
     *     name="users_delete",
     *     methods={"DELETE"},
     *     requirements={"id":"\d+"}
     * )
     * Deletes a user with the specified route id parameter
     */
    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(
                ResponseService::getErrorResponse(self::INVALID_USER),
                Response::HTTP_BAD_REQUEST
            );
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(
            ResponseService::getSuccessResponse(
                null,
                self::USER_DELETED_SUCCESS,
                Response::HTTP_NO_CONTENT
            ),
            Response::HTTP_NO_CONTENT
        );
    }

    /**
     * @Route(
     *     "/{id}/add-group",
     *     name="users_add_group",
     *     methods={"POST"},
     *     requirements={"id":"\d+"}
     * )
     * Adds user to group
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function addGroup(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);
        $responseBody = $request->getContent();

        if (!$user) {
            return $this->json(
                ResponseService::getErrorResponse(self::INVALID_USER),
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!$responseBody) {
            return $this->json(
                ResponseService::getErrorResponse(self::INVALID_GROUP),
                Response::HTTP_BAD_REQUEST
            );
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $userGroup = $serializer->deserialize(
            $request->getContent(),
            Group::class,
            'json'
        );
        $group = $entityManager->getRepository(Group::class)
            ->findOneBy(['name' => $userGroup->getName()]);

        if (!$group) {
            return $this->json(
                ResponseService::getErrorResponse(self::INVALID_GROUP),
                Response::HTTP_BAD_REQUEST
            );
        }

        $user->addGroup($group);
        $entityManager->flush();

        return $this->json(
            ResponseService::getSuccessResponse(
                $user,
                self::ADD_GROUP_SUCCESS,
                Response::HTTP_CREATED
            ),
            Response::HTTP_CREATED
        );
    }

    /**
     * @Route(
     *     "/{id}/delete-group",
     *     name="users_delete_group",
     *     methods={"DELETE"},
     *     requirements={"id":"\d+"}
     *)
     * Removes a valid user from group
     * @param Request $request
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteGroup(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(
                ResponseService::getErrorResponse(self::INVALID_USER),
                Response::HTTP_BAD_REQUEST
            );
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $userGroup = $serializer->deserialize(
            $request->getContent(),
            Group::class,
            'json'
        );
        $group = $entityManager->getRepository(Group::class)
            ->findOneBy(['name' => $userGroup->getName()]);

        if (!$group) {
            return $this->json(
                ResponseService::getErrorResponse(self::INVALID_GROUP),
                Response::HTTP_BAD_REQUEST
            );
        }

        $user->removeGroup($group);
        $entityManager->flush();

        return $this->json(
            ResponseService::getSuccessResponse(
                null,
                self::DELETE_GROUP_SUCCESS,
                Response::HTTP_NO_CONTENT
            ),
            Response::HTTP_NO_CONTENT
        );
    }

}
