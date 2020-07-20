<?php

namespace App\Entity;

use App\Repository\GeneralMeetingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GeneralMeetingRepository::class)
 */
class GeneralMeeting
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
     * @ORM\Column(type="datetime")
     * data zgromadzenia
     */
    private $startDate;

    /**
     * @ORM\Column(type="integer")
     * wariant głosowania
     * 1 - nad uchwałą
     * 2 - personalne
     */
    private $variant;

    /**
     * @ORM\Column(type="integer")
     * ilość głosów/kandydatów
     */
    private $count;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     * opcja wstrzymuje się
     */
    private $holdBack;

    /**
     * @ORM\Column(type="integer")
     * waga głosów/akcji
     * 1 - waga głosów
     * 2 - waga akcji
     */
    private $weight;

    /**
     * @ORM\Column(type="boolean")
     * głosowanie tajne
     */
    private $secret;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * ustawienia błędnej ilości odpowiedzi
     */
    private $badVoteSettings;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * ilość możliwych kandydatów do wybrania
     */
    private $toChoose;

    /**
     * @ORM\ManyToOne(targetEntity=Room::class, inversedBy="generalMeetings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $room;

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

    public function getStartDate(): ?\DateTimeInterface
    {
        return $this->startDate;
    }

    public function setStartDate(\DateTimeInterface $startDate): self
    {
        $this->startDate = $startDate;

        return $this;
    }

    public function getVariant(): ?int
    {
        return $this->variant;
    }

    public function setVariant(int $variant): self
    {
        $this->variant = $variant;

        return $this;
    }

    public function getCount(): ?int
    {
        return $this->count;
    }

    public function setCount(int $count): self
    {
        $this->count = $count;

        return $this;
    }

    public function getHoldBack(): ?bool
    {
        return $this->holdBack;
    }

    public function setHoldBack(bool $holdBack): self
    {
        $this->holdBack = $holdBack;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getSecret(): ?bool
    {
        return $this->secret;
    }

    public function setSecret(bool $secret): self
    {
        $this->secret = $secret;

        return $this;
    }

    public function getBadVoteSettings(): ?int
    {
        return $this->badVoteSettings;
    }

    public function setBadVoteSettings(int $badVoteSettings): self
    {
        $this->badVoteSettings = $badVoteSettings;

        return $this;
    }

    public function getToChoose(): ?int
    {
        return $this->toChoose;
    }

    public function setToChoose(?int $toChoose): self
    {
        $this->toChoose = $toChoose;

        return $this;
    }

    public function getRoom(): ?Room
    {
        return $this->room;
    }

    public function setRoom(?Room $room): self
    {
        $this->room = $room;

        return $this;
    }
}
