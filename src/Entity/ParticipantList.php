<?php

namespace App\Entity;

use App\Repository\ParticipantListRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass=ParticipantListRepository::class)
 */
class ParticipantList
{
    use TimestampableEntity;

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="participantLists")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hashId;

    /**
     * @ORM\Column(type="boolean")
     */
    private $open=false;

    /**
     * @ORM\OneToMany(targetEntity=Participant::class, mappedBy="list")
     */
    private $participants;

    /**
     * @ORM\OneToMany(targetEntity=GeneralMeeting::class, mappedBy="participantList")
     */
    private $meeting;


    public function __construct()
    {
        $this->meeting = new ArrayCollection();
        $this->participants = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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


    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getHashId(): ?string
    {
        return $this->hashId;
    }

    public function setHashId(string $hashId): self
    {
        $this->hashId = $hashId;

        return $this;
    }

    public function getOpen(): ?bool
    {
        return $this->open;
    }

    public function setOpen(bool $open): self
    {
        $this->open = $open;

        return $this;
    }

    /**
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
            $participant->setList($this);
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
            // set the owning side to null (unless already changed)
            if ($participant->getList() === $this) {
                $participant->setList(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|GeneralMeeting[]
     */
    public function getMeeting(): Collection
    {
        return $this->meeting;
    }

    public function addMeeting(GeneralMeeting $meeting): self
    {
        if (!$this->meeting->contains($meeting)) {
            $this->meeting[] = $meeting;
            $meeting->setParticipantList($this);
        }

        return $this;
    }

    public function removeMeeting(GeneralMeeting $meeting): self
    {
        if ($this->meeting->contains($meeting)) {
            $this->meeting->removeElement($meeting);
            // set the owning side to null (unless already changed)
            if ($meeting->getParticipantList() === $this) {
                $meeting->setParticipantList(null);
            }
        }

        return $this;
    }

}
