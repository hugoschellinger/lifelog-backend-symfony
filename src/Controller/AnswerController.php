<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Question;
use App\Entity\ResponseSession;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/answers', name: 'api_answer_')]
class AnswerController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('/session/{sessionId}', name: 'list', methods: ['GET'])]
    public function list(string $sessionId): JsonResponse
    {
        $session = $this->em->getRepository(ResponseSession::class)->find($sessionId);
        if (!$session) {
            return new JsonResponse(['error' => 'Response session not found'], Response::HTTP_NOT_FOUND);
        }

        $answers = $session->getAnswers()->toArray();
        $data = $this->serializer->serialize($answers, 'json', ['groups' => ['answer:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $answer = $this->em->getRepository(Answer::class)->find($id);
        if (!$answer) {
            return new JsonResponse(['error' => 'Answer not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = $this->serializer->serialize($answer, 'json', ['groups' => ['answer:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/question/{questionId}/session/{sessionId}', name: 'create', methods: ['POST'])]
    public function create(string $questionId, string $sessionId, Request $request): JsonResponse
    {
        $question = $this->em->getRepository(Question::class)->find($questionId);
        if (!$question) {
            return new JsonResponse(['error' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }

        $session = $this->em->getRepository(ResponseSession::class)->find($sessionId);
        if (!$session) {
            return new JsonResponse(['error' => 'Response session not found'], Response::HTTP_NOT_FOUND);
        }

        $answer = $this->serializer->deserialize($request->getContent(), Answer::class, 'json', ['groups' => ['answer:write']]);
        $answer->setQuestion($question);
        $answer->setResponseSession($session);
        
        $this->em->persist($answer);
        $this->em->flush();

        $responseData = $this->serializer->serialize($answer, 'json', ['groups' => ['answer:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $answer = $this->em->getRepository(Answer::class)->find($id);
        if (!$answer) {
            return new JsonResponse(['error' => 'Answer not found'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize($request->getContent(), Answer::class, 'json', [
            'groups' => ['answer:write'],
            'object_to_populate' => $answer
        ]);
        
        $this->em->flush();

        $responseData = $this->serializer->serialize($answer, 'json', ['groups' => ['answer:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $answer = $this->em->getRepository(Answer::class)->find($id);
        if (!$answer) {
            return new JsonResponse(['error' => 'Answer not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($answer);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

