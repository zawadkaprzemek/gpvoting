<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\RoomRepository")
 */
class Room
{
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Event", inversedBy="rooms")
     * @ORM\JoinColumn(nullable=false)
     */
    private $event;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128)
     */
    private $slug;

    /**
     * @ORM\Column(type="boolean")
     */
    private $visible=false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Polling", mappedBy="room")
     */
    private $pollings;

    /**
     * @ORM\OneToMany(targetEntity=GeneralMeeting::class, mappedBy="room")
     */
    private $generalMeetings;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $code;
    

    public function __construct()
    {
        $this->pollings = new ArrayCollection();
        $this->generalMeetings = new ArrayCollection();
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

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(?Event $event): self
    {
        $this->event = $event;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getVisible(): ?bool
    {
        return $this->visible;
    }

    public function setVisible(bool $visible): self
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * @return Collection|Polling[]
     */
    public function getPollings(): Collection
    {
        return $this->pollings;
    }

    public function addPolling(Polling $polling): self
    {
        if (!$this->pollings->contains($polling)) {
            $this->pollings[] = $polling;
            $polling->setRoom($this);
        }

        return $this;
    }

    public function removePolling(Polling $polling): self
    {
        if ($this->pollings->contains($polling)) {
            $this->pollings->removeElement($polling);
            // set the owning side to null (unless already changed)
            if ($polling->getRoom() === $this) {
                $polling->setRoom(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|GeneralMeeting[]
     */
    public function getGeneralMeetings(): Collection
    {
        return $this->generalMeetings;
    }

    public function addGeneralMeeting(GeneralMeeting $generalMeeting): self
    {
        if (!$this->generalMeetings->contains($generalMeeting)) {
            $this->generalMeetings[] = $generalMeeting;
            $generalMeeting->setRoom($this);
        }

        return $this;
    }

    public function removeGeneralMeeting(GeneralMeeting $generalMeeting): self
    {
        if ($this->generalMeetings->contains($generalMeeting)) {
            $this->generalMeetings->removeElement($generalMeeting);
            // set the owning side to null (unless already changed)
            if ($generalMeeting->getRoom() === $this) {
                $generalMeeting->setRoom(null);
            }
        }

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }
}
