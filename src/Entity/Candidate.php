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
     * @ORM\ManyToOne(targetEntity=GeneralMeeting::class, inversedBy="candidates",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $general_meeting;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="array")
     */
    private $votes_count;

    /**
     * @ORM\Column(type="array")
     */
    private $actions_count = [];

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

    public function getVotesCount(): ?array
    {
        return $this->votes_count;
    }

    public function setVotesCount(array $votes_count): self
    {
        $this->votes_count = $votes_count;

        return $this;
    }

    public function getActionsCount(): ?array
    {
        return $this->actions_count;
    }

    public function setActionsCount(array $actionsCount): self
    {
        $this->actions_count = $actionsCount;

        return $this;
    }
}
