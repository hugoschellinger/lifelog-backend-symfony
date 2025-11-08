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
    public function list(int $sessionId): JsonResponse
    {
        $session = $this->em->getRepository(ResponseSession::class)->find($sessionId);
        if (!$session) {
            return new JsonResponse(['error' => 'Response session not found'], Response::HTTP_NOT_FOUND);
        }

        $answers = $session->getAnswers()->toArray();
        
        try {
            $data = $this->serializer->serialize($answers, 'json', ['groups' => ['answer:read']]);
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            $data = array_map(fn(Answer $answer) => $this->serializeAnswer($answer), $answers);
            return new JsonResponse($data, Response::HTTP_OK);
        }
    }
    
    private function serializeAnswer(Answer $answer): array
    {
        $data = [
            'id' => $answer->getId(),
            'text_value' => $answer->getTextValue(),
            'number_value' => $answer->getNumberValue(),
            'date_value' => $answer->getDateValue() ? $answer->getDateValue()->format('c') : null,
            'bool_value' => $answer->getBoolValue(),
            'selected_options' => $answer->getSelectedOptions(),
            'answered_at' => $answer->getAnsweredAt()->format('c'),
        ];
        
        // Ajouter la question si elle existe
        if ($answer->getQuestion()) {
            $question = $answer->getQuestion();
            $data['question'] = [
                'id' => $question->getId(),
                'title' => $question->getTitle(),
                'question_description' => $question->getQuestionDescription(),
                'type' => $question->getType()->value,
                'is_required' => $question->isRequired(),
                'is_active' => $question->isActive(),
                'order' => $question->getOrder(),
                'options' => $question->getOptions(),
                'min_value' => $question->getMinValue(),
                'max_value' => $question->getMaxValue(),
                'created_at' => $question->getCreatedAt()->format('c'),
            ];
        }
        
        // Ajouter la session si elle existe
        if ($answer->getResponseSession()) {
            $responseSession = $answer->getResponseSession();
            $data['response_session'] = [
                'id' => $responseSession->getId(),
                'session_date' => $responseSession->getSessionDate()->format('c'),
                'is_completed' => $responseSession->isCompleted(),
                'completion_date' => $responseSession->getCompletionDate() ? $responseSession->getCompletionDate()->format('c') : null,
                'session_title' => $responseSession->getSessionTitle(),
            ];
        }
        
        return $data;
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $answer = $this->em->getRepository(Answer::class)->find($id);
        if (!$answer) {
            return new JsonResponse(['error' => 'Answer not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $data = $this->serializer->serialize($answer, 'json', ['groups' => ['answer:read']]);
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            return new JsonResponse($this->serializeAnswer($answer), Response::HTTP_OK);
        }
    }

    #[Route('/question/{questionId}/session/{sessionId}', name: 'create', methods: ['POST'])]
    public function create(int $questionId, int $sessionId, Request $request): JsonResponse
    {
        $question = $this->em->getRepository(Question::class)->find($questionId);
        if (!$question) {
            return new JsonResponse(['error' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }

        $session = $this->em->getRepository(ResponseSession::class)->find($sessionId);
        if (!$session) {
            return new JsonResponse(['error' => 'Response session not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $textValue = $payload['text_value'] ?? ($payload['textValue'] ?? null);
        $numberValue = $payload['number_value'] ?? ($payload['numberValue'] ?? null);
        $dateValueRaw = $payload['date_value'] ?? ($payload['dateValue'] ?? null);
        $boolValue = $payload['bool_value'] ?? ($payload['boolValue'] ?? null);
        $selectedOptions = $payload['selected_options'] ?? ($payload['selectedOptions'] ?? []);
        $answeredAtRaw = $payload['answered_at'] ?? ($payload['answeredAt'] ?? null);

        $dateValue = null;
        if ($dateValueRaw) {
            try {
                $dateValue = new \DateTime($dateValueRaw);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid date_value format'], Response::HTTP_BAD_REQUEST);
            }
        }

        $answeredAt = new \DateTime();
        if ($answeredAtRaw) {
            try {
                $answeredAt = new \DateTime($answeredAtRaw);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid answered_at format'], Response::HTTP_BAD_REQUEST);
            }
        }

        $answer = new Answer();
        $answer
            ->setTextValue($textValue)
            ->setNumberValue($numberValue !== null ? (float)$numberValue : null)
            ->setDateValue($dateValue)
            ->setBoolValue($boolValue)
            ->setSelectedOptions($selectedOptions)
            ->setAnsweredAt($answeredAt)
            ->setQuestion($question)
            ->setResponseSession($session);
        
        $this->em->persist($answer);
        $this->em->flush();

        try {
            $responseData = $this->serializer->serialize($answer, 'json', ['groups' => ['answer:read']]);
            return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            return new JsonResponse($this->serializeAnswer($answer), Response::HTTP_CREATED);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
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

        try {
            $responseData = $this->serializer->serialize($answer, 'json', ['groups' => ['answer:read']]);
            return new JsonResponse(json_decode($responseData, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            return new JsonResponse($this->serializeAnswer($answer), Response::HTTP_OK);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
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

