<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;


/**
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{
    const UPLOAD_DIRECTORY="assets/uploads/images/";

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
    private $organizer;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $logo;

    private $logoPath;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Room", mappedBy="event")
     */
    private $rooms;

    /**
     * @Gedmo\Slug(fields={"organizer", "name"})
     * @ORM\Column(length=128, unique=true)
     */
    private $slug;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\EventCode", mappedBy="event")
     */
    private $eventCodes;

    /**
     * @ORM\Column(type="smallint")
     * 0 - ukryty
     * 1 - aktywny
     * 2 - zamknięty
     */
    private $status=0;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="events")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    public function __construct()
    {
        $this->eventCodes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOrganizer(): ?string
    {
        return $this->organizer;
    }

    public function setOrganizer(string $organizer): self
    {
        $this->organizer = $organizer;

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


    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    /**
     * @return Collection|Room[]
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }

    public function addRoom(Room $room): self
    {
        if (!$this->rooms->contains($room)) {
            $this->rooms[] = $room;
            $room->setEvent($this);
        }

        return $this;
    }

    public function removeRoom(Room $room): self
    {
        if ($this->rooms->contains($room)) {
            $this->rooms->removeElement($room);
            // set the owning side to null (unless already changed)
            if ($room->getEvent() === $this) {
                $room->setEvent(null);
            }
        }

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

    /**
     * @return Collection|EventCode[]
     */
    public function getEventCodes(): Collection
    {
        return $this->eventCodes;
    }

    public function addEventCode(EventCode $eventCode): self
    {
        if (!$this->eventCodes->contains($eventCode)) {
            $this->eventCodes[] = $eventCode;
            $eventCode->setEvent($this);
        }

        return $this;
    }

    public function removeEventCode(EventCode $eventCode): self
    {
        if ($this->eventCodes->contains($eventCode)) {
            $this->eventCodes->removeElement($eventCode);
            // set the owning side to null (unless already changed)
            if ($eventCode->getEvent() === $this) {
                $eventCode->setEvent(null);
            }
        }

        return $this;
    }

    public function getLogoPath()
    {
        return self::UPLOAD_DIRECTORY.$this->getLogo();
    }

    public function __toString()
    {
        return $this->name;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }
}
