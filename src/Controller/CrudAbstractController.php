<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\AbstractService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

abstract class CrudAbstractController extends AbstractController
{

    protected string $class;
    protected AbstractService $service;
    protected array $context;

    public function __construct(string $class, AbstractService $service, array $context)
    {
        $this->class = $class;
        $this->service = $service;
        $this->context = $context;
    }


    #[Route(path: "", name: "getAll", methods: ["GET"])]
    public function getAll(): Response
    {
        return $this->json($this->service->findAll(), 200, [], $this->context);
    }

    #[Route(path: "", name: "createOne", methods: ["POST"])]
    public function createOne(Request $request, SerializerInterface $serializer): Response
    {
        $entity = $serializer->deserialize($request->getContent(), User::class, "json");
        $this->service->save($entity);

        return $this->json($entity, 201, [], $this->context);
    }

    #[Route(path: "/{entity}", name: "getOne", methods: ["GET"])]
    public function getOne(string $entity): Response
    {
        return $this->json($entity, 200, [], $this->context);
    }

    #[Route(path: "/{entity}", name: "modifyOne", methods: ["PUT"])]
    public function modifyOne(string $entity, Request $request, SerializerInterface $serializer): Response
    {
        $serializer->deserialize($request->getContent(), User::class, "json", [...$this->context, AbstractNormalizer::OBJECT_TO_POPULATE => $entity]);
        $this->service->save($entity);

        return $this->json($entity, 202, [], $this->context);
    }

    #[Route(path: "/{entity}", name: "deleteOne", methods: ["DELETE"])]
    public function deleteOne(string $entity): Response
    {
        $this->service->delete($entity);

        return $this->json("OK", 200, [], $this->context);
    }
}
