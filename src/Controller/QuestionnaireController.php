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
        if (!$year || !$year->getQuestionnaire()) {
            return new JsonResponse(['error' => 'Questionnaire not found'], Response::HTTP_NOT_FOUND);
        }
        
        $data = $this->serializer->serialize($year->getQuestionnaire(), 'json', ['groups' => ['questionnaire:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
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
            $this->em->remove($year->getQuestionnaire());
        }

        $questionnaire = $this->serializer->deserialize($request->getContent(), Questionnaire::class, 'json', ['groups' => ['questionnaire:write']]);
        $questionnaire->setYear($year);
        
        $this->em->persist($questionnaire);
        $this->em->flush();

        $responseData = $this->serializer->serialize($questionnaire, 'json', ['groups' => ['questionnaire:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'update', methods: ['PUT'])]
    public function update(string $id, Request $request): JsonResponse
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
    public function delete(string $id): JsonResponse
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

