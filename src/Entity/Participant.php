<?php

namespace App\Entity;

use App\Repository\ParticipantRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=ParticipantRepository::class)
 * @UniqueEntity(
 *     fields={"email","list"},
 *     errorPath="email",
 *     message="Jesteś już zapisany do tej listy"
 * )
 */
class Participant
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
     * @ORM\Column(type="string", length=255)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="integer")
     */
    private $votes;

    /**
     * @ORM\Column(type="integer")
     */
    private $actions;

    /**
     * @ORM\ManyToOne(targetEntity=ParticipantList::class, inversedBy="participants")
     * @ORM\JoinColumn(nullable=false)
     */
    private $list;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $plainPass;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $Aid;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $accepted=false;

    /**
     * @ORM\Column(type="boolean")
     */
    private $verified=false;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hash;

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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getVotes(): ?int
    {
        return $this->votes;
    }

    public function setVotes(int $votes): self
    {
        $this->votes = $votes;

        return $this;
    }

    public function getActions(): ?int
    {
        return $this->actions;
    }

    public function setActions(int $actions): self
    {
        $this->actions = $actions;

        return $this;
    }

    public function getList(): ?ParticipantList
    {
        return $this->list;
    }

    public function setList(?ParticipantList $list): self
    {
        $this->list = $list;

        return $this;
    }

    public function getPlainPass(): ?string
    {
        return $this->plainPass;
    }

    public function setPlainPass(string $plainPass): self
    {
        $this->plainPass = $plainPass;

        return $this;
    }

    public function getAid(): ?string
    {
        return $this->Aid;
    }

    public function setAid(string $Aid): self
    {
        $this->Aid = $Aid;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAccepted(): ?bool
    {
        return $this->accepted;
    }

    public function setAccepted(bool $accepted): self
    {
        $this->accepted = $accepted;

        return $this;
    }

    /**
     * @return bool
     */
    public function getVerified(): bool
    {
        return $this->verified;
    }

    /**
     * @param bool $verified
     * @return Participant
     */
    public function setVerified(bool $verified): self
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param mixed $hash
     */
    public function setHash($hash): void
    {
        $this->hash = $hash;
    }
}
