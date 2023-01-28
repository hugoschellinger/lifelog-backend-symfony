<?php

namespace App\Service;

interface InterfaceService
{

    public function find($id);
    public function findOneBy(array $criteria, ?array $orderBy = null);
    public function findAll();
    public function findBy(
        array $criteria,
        ?array $orderBy = null,
        $limit = null,
        $offset = null
    );
    public function save($entity);
}
