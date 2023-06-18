<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture{

    //php bin/console d:f:l --append
    private UserPasswordHasherInterface $hasher;

    public function __construct(UserPasswordHasherInterface $hasher)
    {
        $this->hasher=$hasher;
    }

    public function load(ObjectManager $manager){
        $user = new User();

        $user->setEmail("admin");
        $user->setFirstname("admin");
        $user->setLastname("admin");
        $user->setRoles(["ROLE_ADMIN"]);
        $passwordHashed=$this->hasher->hashPassword($user,"admin");
        $user->setPassword($passwordHashed);

        $manager->persist($user);
        $manager->flush();
    }
    
}