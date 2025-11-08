<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\GlobalObjective;
use App\Entity\ObjectiveType;
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
    public function list(int $globalObjectiveId): JsonResponse
    {
        $globalObjective = $this->em->getRepository(GlobalObjective::class)->find($globalObjectiveId);
        if (!$globalObjective) {
            return new JsonResponse(['error' => 'Global objective not found'], Response::HTTP_NOT_FOUND);
        }

        $goals = $globalObjective->getGoals()->toArray();
        
        try {
            $data = $this->serializer->serialize($goals, 'json', ['groups' => ['goal:read']]);
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            $data = array_map(fn(Goal $goal) => $this->serializeGoal($goal), $goals);
            return new JsonResponse($data, Response::HTTP_OK);
        }
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $goal = $this->em->getRepository(Goal::class)->find($id);
        if (!$goal) {
            return new JsonResponse(['error' => 'Goal not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $data = $this->serializer->serialize($goal, 'json', ['groups' => ['goal:read']]);
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            return new JsonResponse($this->serializeGoal($goal), Response::HTTP_OK);
        }
    }

    #[Route('/global-objective/{globalObjectiveId}', name: 'create', methods: ['POST'])]
    public function create(int $globalObjectiveId, Request $request): JsonResponse
    {
        $globalObjective = $this->em->getRepository(GlobalObjective::class)->find($globalObjectiveId);
        if (!$globalObjective) {
            return new JsonResponse(['error' => 'Global objective not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $title = $payload['title'] ?? null;
        $description = $payload['goal_description'] ?? ($payload['goalDescription'] ?? null);
        $measure = $payload['measure'] ?? null;
        $measureLabel = $payload['measure_label'] ?? ($payload['measureLabel'] ?? null);
        $targetDateRaw = $payload['target_date'] ?? ($payload['targetDate'] ?? null);
        $typeValue = $payload['type'] ?? null;

        if (!$title || $measure === null || !$measureLabel || !$targetDateRaw || !$typeValue) {
            return new JsonResponse(['error' => 'Missing fields: title, measure, measure_label, target_date, type'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $type = ObjectiveType::from($typeValue);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => 'Invalid type value'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $targetDate = new \DateTime($targetDateRaw);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Invalid target_date format'], Response::HTTP_BAD_REQUEST);
        }

        $goal = new Goal();
        $goal
            ->setTitle($title)
            ->setGoalDescription($description)
            ->setMeasure((float)$measure)
            ->setMeasureLabel($measureLabel)
            ->setTargetDate($targetDate)
            ->setType($type)
            ->setGlobalObjective($globalObjective);

        $this->em->persist($goal);
        $this->em->flush();

        try {
            $responseData = $this->serializer->serialize($goal, 'json', ['groups' => ['goal:read']]);
            return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            return new JsonResponse($this->serializeGoal($goal), Response::HTTP_CREATED);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
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

        try {
            $responseData = $this->serializer->serialize($goal, 'json', ['groups' => ['goal:read']]);
            return new JsonResponse(json_decode($responseData, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            return new JsonResponse($this->serializeGoal($goal), Response::HTTP_OK);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $goal = $this->em->getRepository(Goal::class)->find($id);
        if (!$goal) {
            return new JsonResponse(['error' => 'Goal not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($goal);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    private function serializeGoal(Goal $goal): array
    {
        $data = [
            'id' => $goal->getId(),
            'title' => $goal->getTitle(),
            'goal_description' => $goal->getGoalDescription(),
            'measure' => $goal->getMeasure(),
            'measure_label' => $goal->getMeasureLabel(),
            'target_date' => $goal->getTargetDate()->format('c'),
            'type' => $goal->getType()->value,
        ];
        
        // Ajouter les progressions
        $progressions = [];
        foreach ($goal->getProgressions() as $progression) {
            $progressions[] = [
                'id' => $progression->getId(),
                'title' => $progression->getTitle(),
                'progression_description' => $progression->getProgressionDescription(),
                'measure' => $progression->getMeasure(),
                'created_at' => $progression->getCreatedAt()->format('c'),
            ];
        }
        $data['progressions'] = $progressions;
        
        return $data;
    }
}

