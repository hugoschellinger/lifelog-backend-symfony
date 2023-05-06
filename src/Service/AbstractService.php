<?php

namespace App\Service;

abstract class AbstractService implements InterfaceService
{

    protected $repository;

    function __construct($repository)
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

    public function delete($entity)
    {
        $this->repository->remove($entity,true);
    }
}
