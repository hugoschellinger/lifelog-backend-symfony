<?php

namespace App\Controller;

use App\Entity\GlobalObjective;
use App\Entity\Year;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/global-objectives', name: 'api_global_objective_')]
class GlobalObjectiveController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/year/{yearValue}', name: 'get_by_year', methods: ['GET'])]
    public function getByYear(int $yearValue): JsonResponse
    {
        $year = $this->em->getRepository(Year::class)->findOneBy(['value' => $yearValue]);
        if (!$year || !$year->getGlobalObjective()) {
            return new JsonResponse(['error' => 'Global objective not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = $this->serializer->serialize($year->getGlobalObjective(), 'json', ['groups' => ['global_objective:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/year/{yearValue}', name: 'create', methods: ['POST'])]
    public function create(int $yearValue, Request $request): JsonResponse
    {
        $year = $this->em->getRepository(Year::class)->findOneBy(['value' => $yearValue]);
        if (!$year) {
            $year = new Year($yearValue);
            $this->em->persist($year);
        }

        // Supprimer l'ancien objectif global s'il existe
        if ($year->getGlobalObjective()) {
            $this->em->remove($year->getGlobalObjective());
        }

        $objective = $this->serializer->deserialize($request->getContent(), GlobalObjective::class, 'json', ['groups' => ['global_objective:write']]);
        $objective->setYear($year);
        
        $this->em->persist($objective);
        $this->em->flush();

        $responseData = $this->serializer->serialize($objective, 'json', ['groups' => ['global_objective:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $objective = $this->em->getRepository(GlobalObjective::class)->find($id);
        if (!$objective) {
            return new JsonResponse(['error' => 'Global objective not found'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize($request->getContent(), GlobalObjective::class, 'json', [
            'groups' => ['global_objective:write'],
            'object_to_populate' => $objective
        ]);
        
        $this->em->flush();

        $responseData = $this->serializer->serialize($objective, 'json', ['groups' => ['global_objective:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $objective = $this->em->getRepository(GlobalObjective::class)->find($id);
        if (!$objective) {
            return new JsonResponse(['error' => 'Global objective not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($objective);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

