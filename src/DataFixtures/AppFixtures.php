<?php

namespace App\DataFixtures;

use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Repository\CampusRepository;
use App\Repository\EtatRepository;
use App\Repository\LieuRepository;
use App\Repository\ParticipantRepository;
use App\Repository\VilleRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Faker\Factory;


class AppFixtures extends Fixture
{

    private UserPasswordHasherInterface $hasher;
    private CampusRepository $campusRepository;
    private EtatRepository $etatRepository;
    private LieuRepository $lieuRepository;
    private ParticipantRepository $participantRepository;
    private VilleRepository $villeRepository;

    public function __construct(VilleRepository $villeRepository, ParticipantRepository $participantRepository, LieuRepository $lieuRepository,UserPasswordHasherInterface $hasher, EtatRepository $etatRepository,CampusRepository $campusRepository)
    {
        $this->hasher = $hasher;
        $this->campusRepository = $campusRepository;
        $this->etatRepository= $etatRepository;
        $this->lieuRepository= $lieuRepository;
        $this->participantRepository= $participantRepository;
        $this->villeRepository= $villeRepository;
    }


    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);

        //$this->addLieux($manager);
        //$this->addParticipants($manager);
        $this->addSorties($manager);

    }

    private function addParticipants(ObjectManager $manager)
    {
        $campus=$this->campusRepository->findAll();

        $generator = Factory::create('fr_FR');

        for ($i = 0; $i < 10; $i++) {
            $user = new Participant();
            $user->setMail($generator->email)
                ->setUsername($generator->userName)
                ->setPrenom($generator->firstName)
                ->setNom($generator->lastName)
                ->setTelephone($generator->phoneNumber)
                ->setActif(1)
                ->setCampus($generator->randomElement($campus))
                ->setRoles(['ROLE_USER'])
                ->setPassword(
                    $this->hasher->hashPassword($user, '123')
                );

            $manager->persist($user);
        }
        $manager->flush();

    }

    private function addSorties(ObjectManager $manager)
    {
        $generator = Factory::create('fr_FR');

        $lieux=$this->lieuRepository->findAll();
        $campus=$this->campusRepository->findAll();
        $etats=$this->etatRepository->findAll();
        $participants=$this->participantRepository->findAll();

        for ($i = 0; $i < 10; $i++) {

            $sortie = new Sortie();

            $sortie->setNom($generator->name)
                ->setDateHeureDebut($generator->dateTimeBetween("now","+1 weeks"))
                ->setDuree($generator->randomNumber(2,false))
                ->setDateLimiteInscription($generator->dateTimeBetween("now","+1 weeks"))
                ->setNbInscriptionsMax($generator->randomNumber(1))
                ->setInfosSortie($generator->name)
                ->setCampus($generator->randomElement($campus))
                ->setEtat($generator->randomElement($etats))
                ->setLieu($generator->randomElement($lieux))
                ->setParticipant($generator->randomElement($participants))
            ;
            $manager->persist($sortie);
        }
        $manager->flush();

    }

    private function addLieux(ObjectManager $manager)
    {
        $generator = Factory::create('fr_FR');
    $villes=$this->villeRepository->findAll();

        for ($i = 0; $i < 10; $i++) {
            $lieu = new Lieu();
            $lieu->setVille($generator->randomElement($villes))
                ->setNom($generator->domainName)
                ->setRue($generator->streetAddress)
            ;


            $manager->persist($lieu);
        }
        $manager->flush();
    }
}
