<?php

namespace App\Controller;

use App\Entity\Question;
use App\Entity\QuestionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/persistent-questions', name: 'persistent_question_')]
class PersistentQuestionController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(Request $request): JsonResponse
    {
        $includeArchived = $request->query->getBoolean('include_archived', false);

        $criteria = ['isPersistent' => true];
        if (!$includeArchived) {
            $criteria['isArchived'] = false;
        }

        $questions = $this->em->getRepository(Question::class)
            ->findBy($criteria, ['order' => 'ASC']);

        $data = $this->serializer->serialize($questions, 'json', ['groups' => ['question:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'get', methods: ['GET'])]
    public function get(int $id): JsonResponse
    {
        $question = $this->em->getRepository(Question::class)->find($id);
        if (!$question || !$question->isPersistent()) {
            return new JsonResponse(['error' => 'Persistent question not found'], Response::HTTP_NOT_FOUND);
        }

        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
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

        if (!$title || !$typeValue) {
            return new JsonResponse(['error' => 'Missing fields: title, type'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $type = QuestionType::from($typeValue);
        } catch (\ValueError $e) {
            return new JsonResponse(['error' => 'Invalid type value'], Response::HTTP_BAD_REQUEST);
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
            ->setMinValue($minValue !== null ? (float) $minValue : null)
            ->setMaxValue($maxValue !== null ? (float) $maxValue : null)
            ->setIsPersistent(true)
            ->setYear(null);

        $this->em->persist($question);
        $this->em->flush();
        $this->em->refresh($question);

        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_CREATED);
    }

    /**
     * Update everything except type.
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $question = $this->em->getRepository(Question::class)->find($id);
        if (!$question || !$question->isPersistent()) {
            return new JsonResponse(['error' => 'Persistent question not found'], Response::HTTP_NOT_FOUND);
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        if (isset($payload['type'])) {
            try {
                $incomingType = QuestionType::from(\is_string($payload['type']) ? $payload['type'] : (string) $payload['type']);
            } catch (\ValueError) {
                return new JsonResponse(['error' => 'Invalid type value'], Response::HTTP_BAD_REQUEST);
            }
            if ($incomingType !== $question->getType()) {
                return new JsonResponse(
                    ['error' => 'The type of a persistent question cannot be modified'],
                    Response::HTTP_UNPROCESSABLE_ENTITY
                );
            }
        }

        if (array_key_exists('title', $payload)) {
            $question->setTitle($payload['title']);
        }
        if (array_key_exists('question_description', $payload) || array_key_exists('questionDescription', $payload)) {
            $question->setQuestionDescription($payload['question_description'] ?? $payload['questionDescription']);
        }
        if (array_key_exists('is_required', $payload) || array_key_exists('isRequired', $payload)) {
            $question->setIsRequired($payload['is_required'] ?? $payload['isRequired']);
        }
        if (array_key_exists('is_active', $payload) || array_key_exists('isActive', $payload)) {
            $question->setIsActive($payload['is_active'] ?? $payload['isActive']);
        }
        if (array_key_exists('order', $payload)) {
            $question->setOrder($payload['order']);
        }
        if (array_key_exists('options', $payload)) {
            $question->setOptions($payload['options']);
        }
        if (array_key_exists('min_value', $payload) || array_key_exists('minValue', $payload)) {
            $val = $payload['min_value'] ?? $payload['minValue'];
            $question->setMinValue($val !== null ? (float) $val : null);
        }
        if (array_key_exists('max_value', $payload) || array_key_exists('maxValue', $payload)) {
            $val = $payload['max_value'] ?? $payload['maxValue'];
            $question->setMaxValue($val !== null ? (float) $val : null);
        }

        $this->em->flush();

        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    /**
     * Archive: keeps existing answers but removes from future questionnaires.
     */
    #[Route('/{id}/archive', name: 'archive', methods: ['PUT'])]
    public function archive(int $id): JsonResponse
    {
        $question = $this->em->getRepository(Question::class)->find($id);
        if (!$question || !$question->isPersistent()) {
            return new JsonResponse(['error' => 'Persistent question not found'], Response::HTTP_NOT_FOUND);
        }

        $question->setIsArchived(true);
        $question->setIsActive(false);
        $this->em->flush();

        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    /**
     * Restore an archived persistent question.
     */
    #[Route('/{id}/restore', name: 'restore', methods: ['PUT'])]
    public function restore(int $id): JsonResponse
    {
        $question = $this->em->getRepository(Question::class)->find($id);
        if (!$question || !$question->isPersistent()) {
            return new JsonResponse(['error' => 'Persistent question not found'], Response::HTTP_NOT_FOUND);
        }

        $question->setIsArchived(false);
        $question->setIsActive(true);
        $this->em->flush();

        $data = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }
}
