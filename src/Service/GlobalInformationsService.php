<?php

use App\Repository\GlobalInformationsRepository;
use App\Service\AbstractService;

class GlobalInformationsService extends AbstractService{

    public function __construct(GlobalInformationsRepository $GlobalInformationsRepository){
        parent::__construct($GlobalInformationsRepository);
    }
}