<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;


class AppFixtures extends Fixture
{
    public function __construct(private UserPasswordHasherInterface $hasher)
    {
    }
    public function load(ObjectManager $manager): void
    {
        $regularUser = new User();
        $regularUser
          ->setEmail('valentin@val.com')
          ->setPassword($this->hasher->hashPassword($regularUser, 'val'));
      
        $manager->persist($regularUser);
      
        $adminUser = new User();
        $adminUser
          ->setEmail('admin@watchthis.com')
          ->setRoles(['ROLE_ADMIN'])
          ->setPassword($this->hasher->hashPassword($adminUser, 'admin'));
      
        $manager->persist($adminUser);
      
        //...autres fixures...
      
        $manager->flush();

        $manager->flush();
    }
}
