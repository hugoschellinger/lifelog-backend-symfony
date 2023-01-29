<?php

namespace App\Service;

use App\Repository\ExceptionRepository;

class ExceptionService extends AbstractService{

    public function __construct(ExceptionRepository $exceptionRepository)
    {
        parent::__construct($exceptionRepository);
    }
}