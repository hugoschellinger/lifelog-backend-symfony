<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\QuestionType;
use App\Entity\Year;
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

    #[Route('/year/{yearValue}', name: 'list', methods: ['GET'])]
    public function list(int $yearValue): JsonResponse
    {
        $year = $this->em->getRepository(Year::class)->findOneBy(['value' => $yearValue]);
        if (!$year) {
            // Créer l'année automatiquement si elle n'existe pas
            $year = new Year($yearValue);
            $this->em->persist($year);
            $this->em->flush();
            $year = $this->em->getRepository(Year::class)->findOneBy(['value' => $yearValue]);
        }

        if (!$year) {
            return new JsonResponse(['error' => 'Year not found'], Response::HTTP_NOT_FOUND);
        }

        $questions = $year->getQuestions()->toArray();
        
        try {
            $data = $this->serializer->serialize($questions, 'json', ['groups' => ['question:read']]);
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            $data = array_map(fn(Question $question) => $this->serializeQuestion($question), $questions);
            return new JsonResponse($data, Response::HTTP_OK);
        }
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $question = $this->em->getRepository(Question::class)->find($id);
        if (!$question) {
            return new JsonResponse(['error' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }
        
        try {
            $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
            return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            return new JsonResponse($this->serializeQuestion($question), Response::HTTP_OK);
        }
    }

    #[Route('/year/{yearValue}', name: 'create', methods: ['POST'])]
    public function create(int $yearValue, Request $request): JsonResponse
    {
        $year = $this->em->getRepository(Year::class)->findOneBy(['value' => $yearValue]);
        if (!$year) {
            // Créer l'année automatiquement si elle n'existe pas
            $year = new Year($yearValue);
            $this->em->persist($year);
            $this->em->flush();
            $year = $this->em->getRepository(Year::class)->findOneBy(['value' => $yearValue]);
        }

        if (!$year) {
            return new JsonResponse(['error' => 'Year not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $title = $payload['title'] ?? null;
        $description = $payload['question_description'] ?? ($payload['questionDescription'] ?? null);
        $typeValue = $payload['type'] ?? null;
        $isRequired = $payload['is_required'] ?? ($payload['isRequired'] ?? false);
        $isActive = $payload['is_active'] ?? ($payload['isActive'] ?? true);
        $order = $payload['order'] ?? 0;
        $options = $payload['options'] ?? [];
        $minValue = $payload['min_value'] ?? ($payload['minValue'] ?? null);
        $maxValue = $payload['max_value'] ?? ($payload['maxValue'] ?? null);
        $createdAtRaw = $payload['created_at'] ?? ($payload['createdAt'] ?? null);

        if (!$title || !$typeValue) {
            return new JsonResponse(['error' => 'Missing fields: title, type'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $type = QuestionType::from($typeValue);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => 'Invalid type value'], Response::HTTP_BAD_REQUEST);
        }

        $createdAt = new \DateTime();
        if ($createdAtRaw) {
            try {
                $createdAt = new \DateTime($createdAtRaw);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid created_at format'], Response::HTTP_BAD_REQUEST);
            }
        }

        $question = new Question();
        $question
            ->setTitle($title)
            ->setQuestionDescription($description)
            ->setType($type)
            ->setIsRequired($isRequired)
            ->setIsActive($isActive)
            ->setOrder($order)
            ->setOptions($options)
            ->setMinValue($minValue !== null ? (float)$minValue : null)
            ->setMaxValue($maxValue !== null ? (float)$maxValue : null)
            ->setCreatedAt($createdAt)
            ->setYear($year);
        
        $this->em->persist($question);
        $this->em->flush();
        $this->em->refresh($question);

        try {
            $responseData = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
            return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            return new JsonResponse($this->serializeQuestion($question), Response::HTTP_CREATED);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
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

        try {
            $responseData = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
            return new JsonResponse(json_decode($responseData, true), Response::HTTP_OK);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            return new JsonResponse($this->serializeQuestion($question), Response::HTTP_OK);
        }
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $question = $this->em->getRepository(Question::class)->find($id);
        if (!$question) {
            return new JsonResponse(['error' => 'Question not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($question);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Sérialise manuellement une question en tableau
     */
    private function serializeQuestion(Question $question): array
    {
        return [
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
            'year' => $question->getYear() ? [
                'id' => $question->getYear()->getId(),
                'value' => $question->getYear()->getValue(),
            ] : null,
            'answers' => [],
        ];
    }
}

