<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $user=new User();
        $user
            ->setRoles(["ROLE_ADMIN"])
            ->setName("GPTeam")
            ->setSurname("GPTeam")
            ->setUsername("GPTeam")
            ->setEmail("gpvoting@gpteam.pl")
            ->setIsVerified(true)
            ->setPassword($this->encoder->encodePassword($user,"GPTeam1234"))
        ;
        $manager->persist($user);

        $manager->flush();
    }
}
