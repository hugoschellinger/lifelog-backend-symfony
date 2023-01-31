<?php

namespace App\DataFixtures;

use App\Entity\GlobalInformations;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class GlobalInformationsFixture extends Fixture{

    //php bin/console d:f:l --append

    public function load(ObjectManager $manager){
        $globalInformations = new GlobalInformations();

        $globalInformations->setName("ALL_REQUEST");
        $globalInformations->setValue(0);

        $manager->persist($globalInformations);
        $manager->flush();
    }
    
}