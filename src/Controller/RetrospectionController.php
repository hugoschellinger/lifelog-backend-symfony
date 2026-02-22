<?php

namespace App\Controller;

use App\Entity\Goal;
use App\Entity\GoalRetrospection;
use App\Entity\Retrospection;
use App\Entity\Year;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/retrospections', name: 'retrospection_')]
class RetrospectionController extends AbstractController
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
        
        if (!$year) {
            return new JsonResponse(null, Response::HTTP_OK);
        }
        
        // Si la rétrospection n'existe pas, retourner null au lieu d'une erreur
        if (!$year->getRetrospection()) {
            return new JsonResponse(null, Response::HTTP_OK);
        }
        
        try {
            $data = $this->serializer->serialize($year->getRetrospection(), 'json', ['groups' => ['retrospection:read']]);
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            $retrospection = $year->getRetrospection();
            
            // Sérialiser les goalRetrospections manuellement
            $goalRetrospections = [];
            foreach ($retrospection->getGoalRetrospections() as $goalRetrospection) {
                try {
                    $goalRetrospectionData = $this->serializer->serialize($goalRetrospection, 'json', ['groups' => ['goal_retrospection:read']]);
                    $goalRetrospectionArray = json_decode($goalRetrospectionData, true);
                    // Ajouter le goalId si disponible
                    if ($goalRetrospection->getGoal()) {
                        $goalRetrospectionArray['goal_id'] = $goalRetrospection->getGoal()->getId();
                    }
                    $goalRetrospections[] = $goalRetrospectionArray;
                } catch (\Exception $e) {
                    // Si la sérialisation échoue, créer manuellement
                    $goalRetrospections[] = [
                        'id' => $goalRetrospection->getId(),
                        'bilan' => $goalRetrospection->getBilan(),
                        'goal_id' => $goalRetrospection->getGoal() ? $goalRetrospection->getGoal()->getId() : null,
                    ];
                }
            }
            
            $data = [
                'id' => $retrospection->getId(),
                'year_reflection' => $retrospection->getYearReflection(),
                'goal_retrospections' => $goalRetrospections,
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
            $this->em->flush();
        }

        // Supprimer l'ancienne rétrospection s'elle existe
        if ($year->getRetrospection()) {
            $this->em->remove($year->getRetrospection());
            $this->em->flush();
        }

        // Désérialisation manuelle
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $yearReflection = $payload['year_reflection'] ?? ($payload['yearReflection'] ?? null);
        $goalRetrospectionsData = $payload['goal_retrospections'] ?? ($payload['goalRetrospections'] ?? []);

        $retrospection = new Retrospection();
        $retrospection->setYearReflection($yearReflection)
            ->setYear($year);
        
        $this->em->persist($retrospection);

        // Créer les GoalRetrospections
        foreach ($goalRetrospectionsData as $goalRetrospectionData) {
            $goalId = $goalRetrospectionData['goal_id'] ?? ($goalRetrospectionData['goalId'] ?? null);
            $bilan = $goalRetrospectionData['bilan'] ?? null;

            if (!$goalId || !$bilan) {
                continue;
            }

            $goal = $this->em->getRepository(Goal::class)->find($goalId);
            if (!$goal) {
                continue;
            }

            $goalRetrospection = new GoalRetrospection();
            $goalRetrospection->setBilan($bilan)
                ->setGoal($goal)
                ->setRetrospection($retrospection);
            
            $this->em->persist($goalRetrospection);
        }
        
        $this->em->flush();

        try {
            $responseData = $this->serializer->serialize($retrospection, 'json', ['groups' => ['retrospection:read']]);
            $responseArray = json_decode($responseData, true);
            
            // Ajouter les goalIds aux goalRetrospections
            if (isset($responseArray['goal_retrospections'])) {
                foreach ($responseArray['goal_retrospections'] as &$gr) {
                    $goalRetrospectionId = $gr['id'] ?? null;
                    if ($goalRetrospectionId) {
                        $grEntity = $this->em->getRepository(GoalRetrospection::class)->find($goalRetrospectionId);
                        if ($grEntity && $grEntity->getGoal()) {
                            $gr['goal_id'] = $grEntity->getGoal()->getId();
                        }
                    }
                }
            }
            
            return new JsonResponse($responseArray, Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Fallback manuel si la sérialisation échoue
            $goalRetrospections = [];
            foreach ($retrospection->getGoalRetrospections() as $gr) {
                $goalRetrospections[] = [
                    'id' => $gr->getId(),
                    'bilan' => $gr->getBilan(),
                    'goal_id' => $gr->getGoal() ? $gr->getGoal()->getId() : null,
                ];
            }
            
            return new JsonResponse([
                'id' => $retrospection->getId(),
                'year_reflection' => $retrospection->getYearReflection(),
                'goal_retrospections' => $goalRetrospections,
            ], Response::HTTP_CREATED);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $retrospection = $this->em->getRepository(Retrospection::class)->find($id);
        if (!$retrospection) {
            return new JsonResponse(['error' => 'Retrospection not found'], Response::HTTP_NOT_FOUND);
        }

        // Désérialisation manuelle
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        // Mettre à jour la rétrospection de l'année
        if (isset($payload['year_reflection']) || isset($payload['yearReflection'])) {
            $yearReflection = $payload['year_reflection'] ?? $payload['yearReflection'];
            $retrospection->setYearReflection($yearReflection);
        }

        // Supprimer les anciennes GoalRetrospections
        foreach ($retrospection->getGoalRetrospections() as $gr) {
            $this->em->remove($gr);
        }
        $this->em->flush();

        // Créer les nouvelles GoalRetrospections
        $goalRetrospectionsData = $payload['goal_retrospections'] ?? ($payload['goalRetrospections'] ?? []);
        foreach ($goalRetrospectionsData as $goalRetrospectionData) {
            $goalId = $goalRetrospectionData['goal_id'] ?? ($goalRetrospectionData['goalId'] ?? null);
            $bilan = $goalRetrospectionData['bilan'] ?? null;

            if (!$goalId || !$bilan) {
                continue;
            }

            $goal = $this->em->getRepository(Goal::class)->find($goalId);
            if (!$goal) {
                continue;
            }

            $goalRetrospection = new GoalRetrospection();
            $goalRetrospection->setBilan($bilan)
                ->setGoal($goal)
                ->setRetrospection($retrospection);
            
            $this->em->persist($goalRetrospection);
        }
        
        $this->em->flush();

        try {
            $responseData = $this->serializer->serialize($retrospection, 'json', ['groups' => ['retrospection:read']]);
            $responseArray = json_decode($responseData, true);
            
            // Ajouter les goalIds aux goalRetrospections
            if (isset($responseArray['goal_retrospections'])) {
                foreach ($responseArray['goal_retrospections'] as &$gr) {
                    $goalRetrospectionId = $gr['id'] ?? null;
                    if ($goalRetrospectionId) {
                        $grEntity = $this->em->getRepository(GoalRetrospection::class)->find($goalRetrospectionId);
                        if ($grEntity && $grEntity->getGoal()) {
                            $gr['goal_id'] = $grEntity->getGoal()->getId();
                        }
                    }
                }
            }
            
            return new JsonResponse($responseArray, Response::HTTP_OK);
        } catch (\Exception $e) {
            // Fallback manuel
            $goalRetrospections = [];
            foreach ($retrospection->getGoalRetrospections() as $gr) {
                $goalRetrospections[] = [
                    'id' => $gr->getId(),
                    'bilan' => $gr->getBilan(),
                    'goal_id' => $gr->getGoal() ? $gr->getGoal()->getId() : null,
                ];
            }
            
            return new JsonResponse([
                'id' => $retrospection->getId(),
                'year_reflection' => $retrospection->getYearReflection(),
                'goal_retrospections' => $goalRetrospections,
            ], Response::HTTP_OK);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $retrospection = $this->em->getRepository(Retrospection::class)->find($id);
        if (!$retrospection) {
            return new JsonResponse(['error' => 'Retrospection not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($retrospection);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

