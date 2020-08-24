<?php

namespace App\Entity;

use App\Repository\ResolutionRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ResolutionRepository::class)
 */
class Resolution
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=GeneralMeeting::class, inversedBy="resolutions",cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $general_meeting;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $content;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $votes_on_count=[];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $votes_against_count=[];

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $votes_hold_count=[];

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $accepted;

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getVotesOnCount(): ?array
    {
        return $this->votes_on_count;
    }

    public function setVotesOnCount(?array $votes_on_count): self
    {
        $this->votes_on_count = $votes_on_count;

        return $this;
    }

    public function getVotesAgainstCount(): ?array
    {
        return $this->votes_against_count;
    }

    public function setVotesAgainstCount(?array $votes_against_count): self
    {
        $this->votes_against_count = $votes_against_count;

        return $this;
    }

    public function getVotesHoldCount(): ?array
    {
        return $this->votes_hold_count;
    }

    public function setVotesHoldCount(?array $votes_hold_count): self
    {
        $this->votes_hold_count = $votes_hold_count;

        return $this;
    }

    public function getAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function setAccepted(?bool $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }
}
