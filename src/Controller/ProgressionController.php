<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\Progression;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/progressions', name: 'api_progression_')]
class ProgressionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/goal/{goalId}', name: 'list', methods: ['GET'])]
    public function list(string $goalId): JsonResponse
    {
        $goal = $this->em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            return new JsonResponse(['error' => 'Goal not found'], Response::HTTP_NOT_FOUND);
        }

        $progressions = $goal->getProgressions()->toArray();
        $data = $this->serializer->serialize($progressions, 'json', ['groups' => ['progression:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $progression = $this->em->getRepository(Progression::class)->find($id);
        if (!$progression) {
            return new JsonResponse(['error' => 'Progression not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = $this->serializer->serialize($progression, 'json', ['groups' => ['progression:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/goal/{goalId}', name: 'create', methods: ['POST'])]
    public function create(string $goalId, Request $request): JsonResponse
    {
        $goal = $this->em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            return new JsonResponse(['error' => 'Goal not found'], Response::HTTP_NOT_FOUND);
        }

        $progression = $this->serializer->deserialize($request->getContent(), Progression::class, 'json', ['groups' => ['progression:write']]);
        $progression->setGoal($goal);
        
        $this->em->persist($progression);
        $this->em->flush();

        $responseData = $this->serializer->serialize($progression, 'json', ['groups' => ['progression:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $progression = $this->em->getRepository(Progression::class)->find($id);
        if (!$progression) {
            return new JsonResponse(['error' => 'Progression not found'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize($request->getContent(), Progression::class, 'json', [
            'groups' => ['progression:write'],
            'object_to_populate' => $progression
        ]);
        
        $this->em->flush();

        $responseData = $this->serializer->serialize($progression, 'json', ['groups' => ['progression:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $progression = $this->em->getRepository(Progression::class)->find($id);
        if (!$progression) {
            return new JsonResponse(['error' => 'Progression not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($progression);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

