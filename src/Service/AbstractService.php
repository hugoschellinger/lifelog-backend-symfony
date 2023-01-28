<?php

namespace App\Service;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

class AbstractService implements InterfaceService
{

    protected ServiceEntityRepository $repository;

    function __construct(ServiceEntityRepository $repository)
    {
        $this->repository=$repository;
    }

    public function find($id)
    {
        return $this->repository->find($id);
    }

    public function findOneBy(array $criteria, ?array $orderBy = null)
    {
        return $this->repository->findOneBy($criteria, $orderBy);
    }

    public function findAll()
    {
        return $this->repository->findAll();
    }

    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        $limit = null,
        $offset = null
    ) {
        return $this->repository->findBy($criteria, $orderBy, $limit, $offset);
    }

    public function save($entity)
    {
        return $this->repository->save($entity,true);
    }
}
