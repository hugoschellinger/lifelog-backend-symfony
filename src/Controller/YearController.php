<?php

namespace App\Controller;

use App\Entity\Year;
use App\Repository\YearRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;

#[Route('/api/years', name: 'api_year_')]
class YearController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        private SerializerInterface $serializer,
        private YearRepository $yearRepository
    ) {
    }

    #[Route('', name: 'list', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $years = $this->yearRepository->findAll();
        $data = $this->serializer->serialize($years, 'json', ['groups' => ['year:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('/{value}', name: 'get', methods: ['GET'], requirements: ['value' => '\\d+'])]
    public function get(int $value): JsonResponse
    {
        $year = $this->yearRepository->findOneBy(['value' => $value]);
        
        // Créer l'année automatiquement si elle n'existe pas
        if (!$year) {
            $year = new Year($value);
            $this->em->persist($year);
            $this->em->flush();
            // Recharger l'entité depuis la base de données pour s'assurer qu'elle est correctement initialisée
            $year = $this->yearRepository->findOneBy(['value' => $value]);
        }
        
        if (!$year) {
            return new JsonResponse(['error' => 'Year not found'], Response::HTTP_NOT_FOUND);
        }
        
        // Créer manuellement le tableau pour éviter les problèmes de sérialisation
        $data = [
            'id' => $year->getId(),
            'value' => $year->getValue(),
        ];
        
        return new JsonResponse($data, Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true);
        if (!is_array($payload)) {
            return new JsonResponse(['error' => 'Invalid JSON body'], Response::HTTP_BAD_REQUEST);
        }

        $value = $payload['value'] ?? null;
        if ($value === null) {
            return new JsonResponse(['error' => 'Missing field: value'], Response::HTTP_BAD_REQUEST);
        }

        // Vérifier si l'année existe déjà
        $existingYear = $this->yearRepository->findOneBy(['value' => $value]);
        if ($existingYear) {
            return new JsonResponse(['error' => 'Year already exists'], Response::HTTP_CONFLICT);
        }

        $year = new Year($value);
        
        $this->em->persist($year);
        $this->em->flush();

        try {
            $responseData = $this->serializer->serialize($year, 'json', ['groups' => ['year:read']]);
            return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            // Si la sérialisation échoue, créer manuellement le tableau
            $data = [
                'id' => $year->getId(),
                'value' => $year->getValue(),
            ];
            return new JsonResponse($data, Response::HTTP_CREATED);
        }
    }

    #[Route('/available', name: 'available', methods: ['GET'])]
    public function available(): JsonResponse
    {
        $currentYear = (int)date('Y');
        $startYear = $currentYear - 9;
        $endYear = $currentYear + 1;
        
        $years = array_reverse(range($startYear, $endYear));
        
        return new JsonResponse($years, Response::HTTP_OK);
    }
}

