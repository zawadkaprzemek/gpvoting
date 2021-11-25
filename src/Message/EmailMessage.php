<?php

namespace App\Message;

use App\Entity\Participant;

final class EmailMessage
{
    /*
     * Add whatever properties & methods you need to hold the
     * data for this message class.
     */

//     private $name;
//
//     public function __construct(string $name)
//     {
//         $this->name = $name;
//     }
//
//    public function getName(): string
//    {
//        return $this->name;
//    }

    private Participant $participant;

    private string $type;

    public function __construct(Participant $participant,string $type)
    {
        $this->participant = $participant;
        $this->type = $type;
    }

    /**
     * @return Participant
     */
    public function getParticipant(): Participant
    {
        return $this->participant;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }


}
