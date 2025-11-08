<?php

namespace App\Controller;

use App\Entity\Questionnaire;
use App\Entity\ResponseSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/response-sessions', name: 'api_response_session_')]
class ResponseSessionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/questionnaire/{questionnaireId}', name: 'list', methods: ['GET'])]
    public function list(int $questionnaireId): JsonResponse
    {
        $questionnaire = $this->em->getRepository(Questionnaire::class)->find($questionnaireId);
        if (!$questionnaire) {
            return new JsonResponse(['error' => 'Questionnaire not found'], Response::HTTP_NOT_FOUND);
        }

        $sessions = $questionnaire->getResponseSessions()->toArray();
        
        // Utiliser toujours la sérialisation manuelle pour garantir que answers_count est inclus
        $data = array_map(fn(ResponseSession $session) => $this->serializeSession($session), $sessions);
        return new JsonResponse($data, Response::HTTP_OK);
    }
    
    private function serializeSession(ResponseSession $session): array
    {
        $answersCount = $session->getAnswers()->count();
        
        return [
            'id' => $session->getId(),
            'session_title' => $session->getSessionTitle(),
            'session_date' => $session->getSessionDate()->format('c'),
            'is_completed' => $session->isCompleted(),
            'completion_date' => $session->getCompletionDate() ? $session->getCompletionDate()->format('c') : null,
            'answers' => [], // Les réponses ne sont pas incluses pour éviter les références circulaires
            'answers_count' => $answersCount, // Nombre de réponses
        ];
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $session = $this->em->getRepository(ResponseSession::class)->find($id);
        if (!$session) {
            return new JsonResponse(['error' => 'Response session not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = $this->serializer->serialize($session, 'json', ['groups' => ['response_session:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/questionnaire/{questionnaireId}', name: 'create', methods: ['POST'])]
    public function create(int $questionnaireId, Request $request): JsonResponse
    {
        $questionnaire = $this->em->getRepository(Questionnaire::class)->find($questionnaireId);
        if (!$questionnaire) {
            return new JsonResponse(['error' => 'Questionnaire not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $sessionTitle = $payload['session_title'] ?? ($payload['sessionTitle'] ?? null);
        $sessionDateRaw = $payload['session_date'] ?? ($payload['sessionDate'] ?? null);
        $isCompleted = $payload['is_completed'] ?? ($payload['isCompleted'] ?? false);
        $completionDateRaw = $payload['completion_date'] ?? ($payload['completionDate'] ?? null);

        $sessionDate = new \DateTime();
        if ($sessionDateRaw) {
            try {
                $sessionDate = new \DateTime($sessionDateRaw);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid session_date format'], Response::HTTP_BAD_REQUEST);
            }
        }

        $completionDate = null;
        if ($completionDateRaw) {
            try {
                $completionDate = new \DateTime($completionDateRaw);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid completion_date format'], Response::HTTP_BAD_REQUEST);
            }
        }

        $session = new ResponseSession();
        $session
            ->setSessionTitle($sessionTitle)
            ->setSessionDate($sessionDate)
            ->setIsCompleted($isCompleted)
            ->setCompletionDate($completionDate)
            ->setQuestionnaire($questionnaire);
        
        $this->em->persist($session);
        $this->em->flush();

        try {
            $responseData = $this->serializer->serialize($session, 'json', ['groups' => ['response_session:read']]);
            return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            $data = [
                'id' => $session->getId(),
                'session_title' => $session->getSessionTitle(),
                'session_date' => $session->getSessionDate()->format('c'),
                'is_completed' => $session->isCompleted(),
                'completion_date' => $session->getCompletionDate() ? $session->getCompletionDate()->format('c') : null,
                'answers' => [],
            ];
            return new JsonResponse($data, Response::HTTP_CREATED);
        }
    }

    #[Route('/{id}/complete', name: 'complete', methods: ['POST'])]
    public function complete(int $id): JsonResponse
    {
        $session = $this->em->getRepository(ResponseSession::class)->find($id);
        if (!$session) {
            return new JsonResponse(['error' => 'Response session not found'], Response::HTTP_NOT_FOUND);
        }

        $session->markAsCompleted();
        $this->em->flush();

        try {
            $responseData = $this->serializer->serialize($session, 'json', ['groups' => ['response_session:read']]);
            return new JsonResponse(json_decode($responseData, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            $data = [
                'id' => $session->getId(),
                'session_title' => $session->getSessionTitle(),
                'session_date' => $session->getSessionDate()->format('c'),
                'is_completed' => $session->isCompleted(),
                'completion_date' => $session->getCompletionDate() ? $session->getCompletionDate()->format('c') : null,
                'answers' => [],
            ];
            return new JsonResponse($data, Response::HTTP_OK);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $session = $this->em->getRepository(ResponseSession::class)->find($id);
        if (!$session) {
            return new JsonResponse(['error' => 'Response session not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($session);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

