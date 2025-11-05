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

    #[Route('/{value}', name: 'get', methods: ['GET'])]
    public function get(int $value): JsonResponse
    {
        $year = $this->yearRepository->findOneBy(['value' => $value]);
        
        // Créer l'année automatiquement si elle n'existe pas
        if (!$year) {
            $year = new Year($value);
            $this->em->persist($year);
            $this->em->flush();
        }
        
        $data = $this->serializer->serialize($year, 'json', ['groups' => ['year:read']]);
        return new JsonResponse(json_decode($data, true), Response::HTTP_OK);
    }

    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $year = $this->serializer->deserialize($request->getContent(), Year::class, 'json', ['groups' => ['year:write']]);
        
        $this->em->persist($year);
        $this->em->flush();

        $responseData = $this->serializer->serialize($year, 'json', ['groups' => ['year:read']]);
        return new JsonResponse(json_decode($responseData, true), Response::HTTP_CREATED);
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

