<?php

namespace App\Controller;

use App\Entity\Questionnaire;
use App\Entity\Year;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/questionnaires', name: 'api_questionnaire_')]
class QuestionnaireController extends AbstractController
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
        
        // Si le questionnaire n'existe pas, retourner null au lieu d'une erreur
        if (!$year->getQuestionnaire()) {
            return new JsonResponse(null, Response::HTTP_OK);
        }
        
        $questionnaire = $year->getQuestionnaire();
        
        // Construire manuellement le tableau pour éviter de charger la collection incorrecte
        // Utiliser les questions de Year au lieu de Questionnaire car la relation est incorrecte
        $questions = [];
        foreach ($year->getQuestions() as $question) {
            try {
                $questionData = $this->serializer->serialize($question, 'json', ['groups' => ['question:read']]);
                $questions[] = json_decode($questionData, true);
            } catch (\Exception $e) {
                // Si la sérialisation d'une question échoue, créer manuellement
                $questions[] = [
                    'id' => $question->getId(),
                    'title' => $question->getTitle(),
                    'question_description' => $question->getQuestionDescription(),
                    'type' => $question->getType()->value,
                    'is_required' => $question->isRequired(),
                    'order' => $question->getOrder(),
                    'options' => $question->getOptions(),
                    'min_value' => $question->getMinValue(),
                    'max_value' => $question->getMaxValue(),
                    'created_at' => $question->getCreatedAt()->format('c'),
                ];
            }
        }
        
        // Sérialiser les sessions de réponse manuellement
        $responseSessions = [];
        // Utiliser une requête DQL pour éviter de charger la collection incorrecte
        $sessions = $this->em->createQuery('SELECT rs FROM App\Entity\ResponseSession rs WHERE rs.questionnaire = :questionnaireId')
            ->setParameter('questionnaireId', $questionnaire->getId())
            ->getResult();
        
        foreach ($sessions as $session) {
            // Utiliser toujours la sérialisation manuelle pour garantir que answers_count est inclus
            $responseSessions[] = [
                'id' => $session->getId(),
                'session_date' => $session->getSessionDate()->format('c'),
                'is_completed' => $session->isCompleted(),
                'completion_date' => $session->getCompletionDate() ? $session->getCompletionDate()->format('c') : null,
                'session_title' => $session->getSessionTitle(),
                'answers' => [], // Les réponses ne sont pas incluses pour éviter les références circulaires
                'answers_count' => $session->getAnswers()->count(), // Nombre de réponses
            ];
        }
        
        $data = [
            'id' => $questionnaire->getId(),
            'title' => $questionnaire->getTitle(),
            'questionnaire_description' => $questionnaire->getQuestionnaireDescription(),
            'created_at' => $questionnaire->getCreatedAt()->format('c'),
            'is_active' => $questionnaire->isActive(),
            'questions' => $questions,
            'response_sessions' => $responseSessions,
        ];
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('/year/{yearValue}', name: 'create', methods: ['POST'])]
    public function create(int $yearValue, Request $request): JsonResponse
    {
        $year = $this->em->getRepository(Year::class)->findOneBy(['value' => $yearValue]);
        if (!$year) {
            $year = new Year($yearValue);
            $this->em->persist($year);
        }

        // Supprimer l'ancien questionnaire s'il existe
        if ($year->getQuestionnaire()) {
            $oldQuestionnaireId = $year->getQuestionnaire()->getId();
            // Supprimer d'abord les sessions de réponse (qui peuvent avoir des réponses)
            $this->em->createQuery('DELETE FROM App\Entity\Answer a WHERE a.responseSession IN (SELECT rs.id FROM App\Entity\ResponseSession rs WHERE rs.questionnaire = :questionnaireId)')
                ->setParameter('questionnaireId', $oldQuestionnaireId)
                ->execute();
            // Supprimer les sessions de réponse
            $this->em->createQuery('DELETE FROM App\Entity\ResponseSession rs WHERE rs.questionnaire = :questionnaireId')
                ->setParameter('questionnaireId', $oldQuestionnaireId)
                ->execute();
            // Supprimer le questionnaire
            $this->em->createQuery('DELETE FROM App\Entity\Questionnaire q WHERE q.id = :id')
                ->setParameter('id', $oldQuestionnaireId)
                ->execute();
            // Réinitialiser la relation
            $year->setQuestionnaire(null);
            $this->em->flush();
        }

        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $title = $payload['title'] ?? null;
        $description = $payload['questionnaire_description'] ?? ($payload['questionnaireDescription'] ?? null);
        $isActive = $payload['is_active'] ?? ($payload['isActive'] ?? true);
        $createdAtRaw = $payload['created_at'] ?? ($payload['createdAt'] ?? null);

        if (!$title) {
            return new JsonResponse(['error' => 'Missing field: title'], Response::HTTP_BAD_REQUEST);
        }

        $createdAt = new \DateTime();
        if ($createdAtRaw) {
            try {
                $createdAt = new \DateTime($createdAtRaw);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid created_at format'], Response::HTTP_BAD_REQUEST);
            }
        }

        $questionnaire = new Questionnaire();
        $questionnaire
            ->setTitle($title)
            ->setQuestionnaireDescription($description ?? '')
            ->setIsActive($isActive)
            ->setCreatedAt($createdAt)
            ->setYear($year);
        
        $this->em->persist($questionnaire);
        $this->em->flush();

        try {
            $responseData = $this->serializer->serialize($questionnaire, 'json', ['groups' => ['questionnaire:read']]);
            return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            $data = [
                'id' => $questionnaire->getId(),
                'title' => $questionnaire->getTitle(),
                'questionnaire_description' => $questionnaire->getQuestionnaireDescription(),
                'created_at' => $questionnaire->getCreatedAt()->format('c'),
                'is_active' => $questionnaire->isActive(),
                'questions' => [],
                'response_sessions' => [],
            ];
            return new JsonResponse($data, Response::HTTP_CREATED);
        }
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $questionnaire = $this->em->getRepository(Questionnaire::class)->find($id);
        if (!$questionnaire) {
            return new JsonResponse(['error' => 'Questionnaire not found'], Response::HTTP_NOT_FOUND);
        }

        $this->serializer->deserialize($request->getContent(), Questionnaire::class, 'json', [
            'groups' => ['questionnaire:write'],
            'object_to_populate' => $questionnaire
        ]);
        
        $this->em->flush();

        $responseData = $this->serializer->serialize($questionnaire, 'json', ['groups' => ['questionnaire:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_OK);
    }

    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $questionnaire = $this->em->getRepository(Questionnaire::class)->find($id);
        if (!$questionnaire) {
            return new JsonResponse(['error' => 'Questionnaire not found'], Response::HTTP_NOT_FOUND);
        }

        $this->em->remove($questionnaire);
        $this->em->flush();

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }
}

