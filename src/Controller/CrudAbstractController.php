<?php
namespace App\Controller;

use App\Service\AbstractService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class CrudAbstractController extends AbstractController{

    protected string $class;
    protected AbstractService $service;
    protected array $context;

    public function __construct(string $class,AbstractService $service,array $context)
    {
        $this->class=$class;
        $this->service=$service;
        $this->context=$context;
    }

}