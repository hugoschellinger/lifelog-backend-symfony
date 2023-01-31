<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

abstract class CrudAbstractController extends AbstractController{

    private string $class;

    public function __construct(string $class)
    {
        $this->class=$class;
    }

}