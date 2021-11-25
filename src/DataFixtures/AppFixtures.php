<?php

namespace App\DataFixtures;

use App\Entity\Pack;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordHasherInterface
     */
    private $encoder;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $pack=new Pack();
        $pack->setName("Pakiet jednorazowy")->setEventsCount(1);
        $manager->persist($pack);

        $user=new User();
        $user
            ->setRoles(["ROLE_ADMIN"])
            ->setName("GPTeam")
            ->setSurname("GPTeam")
            ->setUsername("GPTeam")
            ->setEmail("gpvoting@gpteam.pl")
            ->setIsVerified(true)
            ->setPassword($this->encoder->hashPassword($user,"GPTeam1234"))
            ->setPack($pack)
        ;
        $manager->persist($user);

        $manager->flush();
    }
}
