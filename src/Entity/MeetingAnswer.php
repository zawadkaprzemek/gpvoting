<?php

namespace App\Entity;

use App\Repository\MeetingAnswerRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MeetingAnswerRepository::class)
 */
class MeetingAnswer
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $content;

    /**
     * @ORM\Column(type="boolean")
     */
    private $valid;

    /**
     * @ORM\ManyToOne(targetEntity=MeetingVoting::class, inversedBy="answers")
     */
    private $meetingVoting;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }
    

    public function getValid(): ?bool
    {
        return $this->valid;
    }

    public function setValid(bool $valid): self
    {
        $this->valid = $valid;

        return $this;
    }

    public function getMeetingVoting(): ?MeetingVoting
    {
        return $this->meetingVoting;
    }

    public function setMeetingVoting(?MeetingVoting $meetingVoting): self
    {
        $this->meetingVoting = $meetingVoting;

        return $this;
    }
}
