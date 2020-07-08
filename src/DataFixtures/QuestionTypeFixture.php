<?php

namespace App\DataFixtures;

use App\Entity\QuestionType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class QuestionTypeFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $type=new QuestionType();
        $type->setName('Pojedyńcze')->setCorrectAnswers(1);
        $type2=new QuestionType();
        $type2->setName('Wielokrotnego wyboru')->setCorrectAnswers(2);
        $type3=new QuestionType();
        $type3->setName('Sondażowe')->setCorrectAnswers(0);
        $manager->persist($type);
        $manager->persist($type2);
        $manager->persist($type3);
        $manager->flush();
    }
}
