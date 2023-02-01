<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\UserRepository;

class UserService extends AbstractService
{

    public function __construct(UserRepository $userRepository)
    {
        parent::__construct($userRepository);
    }
}