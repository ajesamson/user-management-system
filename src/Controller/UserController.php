<?php

namespace App\Controller;

use App\Entity\Group;
use App\Entity\User;
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
    /**
     * @Route("/", name="users_index", methods={"GET"})
     */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->findAll();

        return $this->json($user);
    }

    /**
     * @Route("/add", name="users_add", methods={"POST"})
     */
    public function add(Request $request)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $users = $serializer->deserialize($request->getContent(), User::class, 'json');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($users);
        $entityManager->flush();

        return $this->json($users, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}", name="users_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(
                Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            );
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * @Route("/{id}/add-group", name="users_add_group", methods={"POST"}, requirements={"id":"\d+"})
     */
    public function addGroup(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(
                Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            );
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $userGroup = $serializer->deserialize($request->getContent(), Group::class, 'json');
        $group = $entityManager->getRepository(Group::class)->findOneBy(['name' => $userGroup->getName()]);

        if (!$group) {
            return $this->json(
                Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user->addGroup($group);
        $entityManager->flush();

        return $this->json($user, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}/delete-group", name="users_delete_group", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function deleteGroup(Request $request, $id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $user = $entityManager->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(
                Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            );
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $userGroup = $serializer->deserialize($request->getContent(), Group::class, 'json');
        $group = $entityManager->getRepository(Group::class)->findOneBy(['name' => $userGroup->getName()]);

        if (!$group) {
            return $this->json(
                Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user->removeGroup($group);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }

}
