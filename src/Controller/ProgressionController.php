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
    public function list(int $goalId): JsonResponse
    {
        $goal = $this->em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            return new JsonResponse(['error' => 'Goal not found'], Response::HTTP_NOT_FOUND);
        }

        $progressions = $goal->getProgressions()->toArray();
        
        $data = array_map(function(Progression $progression) {
            return $this->serializeProgression($progression);
        }, $progressions);
        
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $progression = $this->em->getRepository(Progression::class)->find($id);
        if (!$progression) {
            return new JsonResponse(['error' => 'Progression not found'], Response::HTTP_NOT_FOUND);
        }
        
        return new JsonResponse($this->serializeProgression($progression), Response::HTTP_OK);
    }

    #[Route('/goal/{goalId}', name: 'create', methods: ['POST'])]
    public function create(int $goalId, Request $request): JsonResponse
    {
        $goal = $this->em->getRepository(Goal::class)->find($goalId);
        if (!$goal) {
            return new JsonResponse(['error' => 'Goal not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $title = $payload["title"] ?? null;
        $description = $payload["progression_description"] ?? ($payload["progressionDescription"] ?? null);
        $measure = $payload["measure"] ?? null;
        $createdAtRaw = $payload["created_at"] ?? ($payload["createdAt"] ?? null);

        if (!$title || $measure === null) {
            return new JsonResponse(['error' => 'Missing fields: title, measure'], Response::HTTP_BAD_REQUEST);
        }

        $createdAt = new \DateTime();
        if ($createdAtRaw) {
            try {
                $createdAt = new \DateTime($createdAtRaw);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid created_at format'], Response::HTTP_BAD_REQUEST);
            }
        }

        $progression = new Progression();
        $progression
            ->setTitle($title)
            ->setProgressionDescription($description)
            ->setMeasure((float)$measure)
            ->setCreatedAt($createdAt)
            ->setGoal($goal);
        
        $this->em->persist($progression);
        $this->em->flush();
        $this->em->refresh($progression);

        return new JsonResponse($this->serializeProgression($progression), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
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
        $this->em->refresh($progression);

        return new JsonResponse($this->serializeProgression($progression), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $progression = $this->em->getRepository(Progression::class)->find($id);
        if (!$progression) {
            return new JsonResponse(['error' => 'Progression not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($progression);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Sérialise manuellement une progression en tableau
     */
    private function serializeProgression(Progression $progression): array
    {
        return [
            'id' => $progression->getId(),
            'title' => $progression->getTitle(),
            'progression_description' => $progression->getProgressionDescription(),
            'measure' => $progression->getMeasure(),
            'created_at' => $progression->getCreatedAt()->format('c'),
        ];
    }
}

