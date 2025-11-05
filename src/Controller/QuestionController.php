<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\Questionnaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/questions', name: 'api_question_')]
class QuestionController extends AbstractController
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

        $questions = $questionnaire->getQuestions()->toArray();
        $data = $this->serializer->serialize($questions, 'json', ['groups' => ['question:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $question = $this->em->getRepository(Question::class)->find($id);
        if (!$question) {
            return new JsonResponse(['error' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/questionnaire/{questionnaireId}', name: 'create', methods: ['POST'])]
    public function create(string $questionnaireId, Request $request): JsonResponse
    {
        $questionnaire = $this->em->getRepository(Questionnaire::class)->find($questionnaireId);
        if (!$questionnaire) {
            return new JsonResponse(['error' => 'Questionnaire not found'], Response::HTTP_NOT_FOUND);
        }

        $question = $this->serializer->deserialize($request->getContent(), Question::class, 'json', ['groups' => ['question:write']]);
        $question->setQuestionnaire($questionnaire);
        
        $this->em->persist($question);
        $this->em->flush();

        $responseData = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
    {
        $question = $this->em->getRepository(Question::class)->find($id);
        if (!$question) {
            return new JsonResponse(['error' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize($request->getContent(), Question::class, 'json', [
            'groups' => ['question:write'],
            'object_to_populate' => $question
        ]);
        
        $this->em->flush();

        $responseData = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(string $id): JsonResponse
    {
        $question = $this->em->getRepository(Question::class)->find($id);
        if (!$question) {
            return new JsonResponse(['error' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($question);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

