<?php

namespace App\Entity;

use App\Repository\MeetingVotingRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=MeetingVotingRepository::class)
 */
class MeetingVoting
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
     * @ORM\ManyToOne(targetEntity=GeneralMeeting::class, inversedBy="meetingVotings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $meeting;

    /**
     * @ORM\Column(type="integer")
     */
    private $type;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $tochoose;

    /**
     * @ORM\Column(type="integer")
     * 0 - oczekujące
     * 1 - w trakcie
     * 2 - zakonczone
     */
    private $status=0;

    /**
     * @ORM\OneToMany(targetEntity=Candidate::class, mappedBy="meetingVoting",cascade={"persist"})
     */
    private $candidates;

    /**
     * @ORM\Column(type="array")
     */
    private $voteStatus = [];

    /**
     * @ORM\Column(type="array")
     */
    private $votesSummary = [];

    /**
     * @ORM\OneToMany(targetEntity=MeetingAnswer::class, mappedBy="meetingVoting",cascade={"persist"})
     */
    private $answers;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    /**
     * @ORM\Column(type="boolean")
     */
    private $multiChoose;

    public function __construct()
    {
        $this->candidates = new ArrayCollection();
        $this->answers = new ArrayCollection();
    }

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

    public function getMeeting(): ?GeneralMeeting
    {
        return $this->meeting;
    }

    public function setMeeting(?GeneralMeeting $meeting): self
    {
        $this->meeting = $meeting;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTochoose(): ?int
    {
        return $this->tochoose;
    }

    public function setTochoose(?int $tochoose): self
    {
        $this->tochoose = $tochoose;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection|Candidate[]
     */
    public function getCandidates(): Collection
    {
        return $this->candidates;
    }

    public function addCandidate(Candidate $candidate): self
    {
        if (!$this->candidates->contains($candidate)) {
            $this->candidates[] = $candidate;
            $candidate->setMeetingVoting($this);
        }

        return $this;
    }

    public function removeCandidate(Candidate $candidate): self
    {
        if ($this->candidates->contains($candidate)) {
            $this->candidates->removeElement($candidate);
            // set the owning side to null (unless already changed)
            if ($candidate->getMeetingVoting() === $this) {
                $candidate->setMeetingVoting(null);
            }
        }

        return $this;
    }

    public function getVoteStatus(): ?array
    {
        return $this->voteStatus;
    }

    public function setVoteStatus(array $voteStatus): self
    {
        $this->voteStatus = $voteStatus;

        return $this;
    }

    public function getVotesSummary(): ?array
    {
        return $this->votesSummary;
    }

    public function setVotesSummary(array $votesSummary): self
    {
        $this->votesSummary = $votesSummary;

        return $this;
    }


    /**
     * @return Collection|MeetingAnswer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(MeetingAnswer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setMeetingVoting($this);
        }

        return $this;
    }

    public function removeAnswer(MeetingAnswer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getMeetingVoting() === $this) {
                $answer->setMeetingVoting(null);
            }
        }

        return $this;
    }

    public function removeCandidates(): self
    {
       foreach ($this->candidates as $candidate)
       {
           $this->candidates->removeElement($candidate);
       }
       return $this;
    }

    public function removeAnswers(): self
    {
        foreach ($this->answers as $answer)
        {
            $this->answers->removeElement($answer);
        }
        return $this;
    }

    public function getSort(): ?int
    {
        return $this->sort;
    }

    public function setSort(int $sort): self
    {
        $this->sort = $sort;

        return $this;
    }

    public function getMultiChoose(): ?bool
    {
        return $this->multiChoose;
    }

    public function setMultiChoose(bool $multiChoose): self
    {
        $this->multiChoose = $multiChoose;

        return $this;
    }
}
