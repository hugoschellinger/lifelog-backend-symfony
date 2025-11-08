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
        
        // Créer l'année automatiquement si elle n'existe pas
        if (!$year) {
            $year = new Year($yearValue);
            $this->em->persist($year);
            $this->em->flush();
            // Recharger l'entité depuis la base de données
            $year = $this->em->getRepository(Year::class)->findOneBy(['value' => $yearValue]);
        }
        
        if (!$year) {
            return new JsonResponse(['error' => 'Year not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Si l'objectif global n'existe pas, retourner null au lieu d'une erreur
        if (!$year->getGlobalObjective()) {
            return new JsonResponse(null, Response::HTTP_OK);
        }
        
        try {
            $data = $this->serializer->serialize($year->getGlobalObjective(), 'json', ['groups' => ['global_objective:read']]);
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            $objective = $year->getGlobalObjective();
            
            // Sérialiser les goals manuellement
            $goals = [];
            foreach ($objective->getGoals() as $goal) {
                try {
                    $goalData = $this->serializer->serialize($goal, 'json', ['groups' => ['goal:read']]);
                    $goals[] = json_decode($goalData, true);
                } catch (\Exception $e) {
                    // Si la sérialisation d'un goal échoue, créer manuellement
                    $goals[] = [
                        'id' => $goal->getId(),
                        'title' => $goal->getTitle(),
                        'goal_description' => $goal->getGoalDescription(),
                        'measure' => $goal->getMeasure(),
                        'measure_label' => $goal->getMeasureLabel(),
                        'target_date' => $goal->getTargetDate()->format('c'),
                        'type' => $goal->getType()->value,
                    ];
                }
            }
            
            $data = [
                'id' => $objective->getId(),
                'title' => $objective->getTitle(),
                'objective_description' => $objective->getObjectiveDescription(),
                'type' => $objective->getType()->value,
                'goals' => $goals,
            ];
            return new JsonResponse($data, Response::HTTP_OK);
        }
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

        // Désérialisation manuelle pour éviter la dépendance aux normalizers
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $title = $payload['title'] ?? null;
        // Rendre la description optionnelle: accepter chaîne vide par défaut
        $description = $payload['objective_description'] ?? ($payload['objectiveDescription'] ?? '');
        $typeValue = $payload['type'] ?? null;

        if (!$title || !$typeValue) {
            return new JsonResponse(['error' => 'Missing fields: title, type'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $type = \App\Entity\ObjectiveType::from($typeValue);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => 'Invalid type value'], Response::HTTP_BAD_REQUEST);
        }

        $objective = new GlobalObjective();
        $objective->setTitle($title)
            ->setObjectiveDescription($description)
            ->setType($type)
            ->setYear($year);
        
        $this->em->persist($objective);
        $this->em->flush();

        try {
            $responseData = $this->serializer->serialize($objective, 'json', ['groups' => ['global_objective:read']]);
            return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Fallback manuel si la sérialisation échoue
            return new JsonResponse([
                'id' => $objective->getId(),
                'title' => $objective->getTitle(),
                'objective_description' => $objective->getObjectiveDescription(),
                'type' => $objective->getType()->value,
                'goals' => [],
            ], Response::HTTP_CREATED);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
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
    public function delete(int $id): JsonResponse
    {
        // La suppression d'un objectif global n'est pas autorisée
        return new JsonResponse(['error' => 'Global objective deletion is not allowed'], Response::HTTP_METHOD_NOT_ALLOWED);
    }
}

