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
    public function list(string $questionnaireId): JsonResponse
    {
        $questionnaire = $this->em->getRepository(Questionnaire::class)->find($questionnaireId);
        if (!$questionnaire) {
            return new JsonResponse(['error' => 'Questionnaire not found'], Response::HTTP_NOT_FOUND);
        }

        $sessions = $questionnaire->getResponseSessions()->toArray();
        $data = $this->serializer->serialize($sessions, 'json', ['groups' => ['response_session:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $session = $this->em->getRepository(ResponseSession::class)->find($id);
        if (!$session) {
            return new JsonResponse(['error' => 'Response session not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = $this->serializer->serialize($session, 'json', ['groups' => ['response_session:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/questionnaire/{questionnaireId}', name: 'create', methods: ['POST'])]
    public function create(string $questionnaireId, Request $request): JsonResponse
    {
        $questionnaire = $this->em->getRepository(Questionnaire::class)->find($questionnaireId);
        if (!$questionnaire) {
            return new JsonResponse(['error' => 'Questionnaire not found'], Response::HTTP_NOT_FOUND);
        }

        $session = $this->serializer->deserialize($request->getContent(), ResponseSession::class, 'json', ['groups' => ['response_session:write']]);
        $session->setQuestionnaire($questionnaire);
        
        $this->em->persist($session);
        $this->em->flush();

        $responseData = $this->serializer->serialize($session, 'json', ['groups' => ['response_session:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
    }

    #[Route('/{id}/complete', name: 'complete', methods: ['POST'])]
    public function complete(string $id): JsonResponse
    {
        $session = $this->em->getRepository(ResponseSession::class)->find($id);
        if (!$session) {
            return new JsonResponse(['error' => 'Response session not found'], Response::HTTP_NOT_FOUND);
        }

        $session->markAsCompleted();
        $this->em->flush();

        $responseData = $this->serializer->serialize($session, 'json', ['groups' => ['response_session:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
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

