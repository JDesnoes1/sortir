<?php

namespace App\DataFixtures;

use App\Entity\Participant;
use App\Repository\CampusRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;


class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;
    private CampusRepository $campusRepository;

    public function __construct(UserPasswordHasherInterface $hasher, CampusRepository $campusRepository)
    {
        $this->hasher = $hasher;
        $this->campusRepository = $campusRepository;

    }


    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $this->addUsers($manager);

    }

    private function addUsers(ObjectManager $manager)
    {
        $generator = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $user = new Participant();
            $user->setMail($generator->email)
                ->setUsername($generator->userName)
                ->setPrenom($generator->firstName)
                ->setNom($generator->lastName)
                ->setTelephone($generator->phoneNumber)
                ->setActif(1)
                ->setCampus($this->campusRepository->find(random_int(1,4)))
                ->setRoles(['ROLE_USER'])
                ->setPassword(
                    $this->hasher->hashPassword($user, '123')
                );

            $manager->persist($user);
        }
        $manager->flush();

    }
}
