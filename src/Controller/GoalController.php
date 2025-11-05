<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\GlobalObjective;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/goals', name: 'api_goal_')]
class GoalController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/global-objective/{globalObjectiveId}', name: 'list', methods: ['GET'])]
    public function list(string $globalObjectiveId): JsonResponse
    {
        $globalObjective = $this->em->getRepository(GlobalObjective::class)->find($globalObjectiveId);
        if (!$globalObjective) {
            return new JsonResponse(['error' => 'Global objective not found'], Response::HTTP_NOT_FOUND);
        }

        $goals = $globalObjective->getGoals()->toArray();
        $data = $this->serializer->serialize($goals, 'json', ['groups' => ['goal:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $goal = $this->em->getRepository(Goal::class)->find($id);
        if (!$goal) {
            return new JsonResponse(['error' => 'Goal not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = $this->serializer->serialize($goal, 'json', ['groups' => ['goal:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/global-objective/{globalObjectiveId}', name: 'create', methods: ['POST'])]
    public function create(string $globalObjectiveId, Request $request): JsonResponse
    {
        $globalObjective = $this->em->getRepository(GlobalObjective::class)->find($globalObjectiveId);
        if (!$globalObjective) {
            return new JsonResponse(['error' => 'Global objective not found'], Response::HTTP_NOT_FOUND);
        }

        $goal = $this->serializer->deserialize($request->getContent(), Goal::class, 'json', ['groups' => ['goal:write']]);
        $goal->setGlobalObjective($globalObjective);
        
        $this->em->persist($goal);
        $this->em->flush();

        $responseData = $this->serializer->serialize($goal, 'json', ['groups' => ['goal:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $goal = $this->em->getRepository(Goal::class)->find($id);
        if (!$goal) {
            return new JsonResponse(['error' => 'Goal not found'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize($request->getContent(), Goal::class, 'json', [
            'groups' => ['goal:write'],
            'object_to_populate' => $goal
        ]);
        
        $this->em->flush();

        $responseData = $this->serializer->serialize($goal, 'json', ['groups' => ['goal:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $goal = $this->em->getRepository(Goal::class)->find($id);
        if (!$goal) {
            return new JsonResponse(['error' => 'Goal not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($goal);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

