<?php

namespace App\Entity;

use App\Repository\CandidateRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CandidateRepository::class)
 */
class Candidate
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=GeneralMeeting::class, inversedBy="candidates")
     * @ORM\JoinColumn(nullable=false)
     */
    private $general_meeting;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $votes_count;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGeneralMeeting(): ?GeneralMeeting
    {
        return $this->general_meeting;
    }

    public function setGeneralMeeting(?GeneralMeeting $general_meeting): self
    {
        $this->general_meeting = $general_meeting;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getVotesCount(): ?int
    {
        return $this->votes_count;
    }

    public function setVotesCount(?int $votes_count): self
    {
        $this->votes_count = $votes_count;

        return $this;
    }
}
