<?php

namespace App\Controller;

use App\Entity\Group;
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
     * @Route("/", name="groups_index", methods={"GET"})
     */
    public function index()
    {
        $entityManager = $this->getDoctrine()->getManager();
        $groups = $entityManager->getRepository(Group::class)->findAll();

        return $this->json($groups);
    }

    /**
     * @Route("/add", name="groups_add", methods={"POST"})
     */
    public function add(Request $request)
    {
        /** @var Serializer $serializer */
        $serializer = $this->get('serializer');
        $group = $serializer->deserialize($request->getContent(), Group::class, 'json');

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($group);
        $entityManager->flush();

        return $this->json($group, Response::HTTP_CREATED);
    }

    /**
     * @Route("/{id}", name="groups_delete", methods={"DELETE"}, requirements={"id":"\d+"})
     */
    public function delete($id)
    {
        $entityManager = $this->getDoctrine()->getManager();
        $group = $entityManager->getRepository(Group::class)->find($id);

        if (!$group) {
            return $this->json(
                Response::$statusTexts[Response::HTTP_BAD_REQUEST],
                Response::HTTP_BAD_REQUEST
            );
        }

        if (!$group->getUsers()->isEmpty()) {
            return $this->json(
                [
                    'error' => 'Group has existing members!'
                ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $entityManager->remove($group);
        $entityManager->flush();

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}
