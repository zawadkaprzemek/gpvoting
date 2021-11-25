<?php

namespace App\Entity;

use App\Repository\GeneralMeetingRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

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
     */
    private $date;

    /**
     * @ORM\Column(type="integer")
     */
    private $count;

    /**
     * @ORM\Column(type="boolean")
     */
    private $secret;

    /**
     * @ORM\Column(type="boolean")
     */
    private $holdBack;

    /**
     * @ORM\Column(type="integer")
     */
    private $weight;

    /**
     * @ORM\Column(type="integer")
     */
    private $badVoteSettings;

    /**
     * @ORM\ManyToOne(targetEntity=Room::class, inversedBy="generalMeetings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $room;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="integer")
     * 0 - nierozpoczete
     * 1 - w trakcie
     * 2 - zakonczone
     */
    private $status=0;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $hashId;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalVotes=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $totalActions=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $absenceVotes=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $absenceActions=0;

    /**
     * @ORM\Column(type="array")
     * 'active' => aktualne głosowanie
     * 'votes' => tabela z glosami obecnościowymi
     * 'last' => poprzednie głosowanie
     * 'voted' => oddali glos w obecnym glosowaniu
     * 'kworum' => osiągnięte wymagane kworum
     * 'kworum_value' => wynik ostatniego kworum
     */
    private $activeStatus = [];

    /**
     * @ORM\OneToMany(targetEntity=MeetingVoting::class, mappedBy="meeting")
     */
    private $meetingVotings;

    /**
     * @ORM\ManyToOne(targetEntity=ParticipantList::class, inversedBy="meeting")
     */
    private $participantList;

    /**
     * @ORM\Column(type="boolean")
     */
    private $resultsForParticipants;

    /**
     * @ORM\Column(type="boolean")
     */
    private $kworum=false;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Wymagany wartość kworum
     */
    private $kworumRequiredValue;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * 1to1|actions|votes
     */
    private $kworumType;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Aktualne głosowanie
     */
    private $active_voting;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * Poprzednie głosowanie
     */
    private $last_voting;

    /**
     * @ORM\Column(type="float", nullable=true)
     * Wynik kworum
     */
    private ?float $kworumValue;

    public function __construct()
    {
        $this->meetingVotings = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id=null;
        $this->activeStatus=[];
        $this->absenceActions=0;
        $this->absenceVotes=0;
        $this->totalVotes=0;
        $this->totalActions=0;
        $this->status=0;
        $this->active_voting=null;
        $this->last_voting=null;
        $this->kworumValue=null;
        $this->hashId=uniqid();
        $this->slug=null;
        $this->name.=" kopia";
        $this->date=new DateTime("+ 1 day");
        $this->meetingVotings = new ArrayCollection();
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

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

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

    public function getSecret(): ?bool
    {
        return $this->secret;
    }

    public function setSecret(bool $secret): self
    {
        $this->secret = $secret;

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

    public function getBadVoteSettings(): ?int
    {
        return $this->badVoteSettings;
    }

    public function setBadVoteSettings(int $badVoteSettings): self
    {
        $this->badVoteSettings = $badVoteSettings;

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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    public function getHashId(): ?string
    {
        return $this->hashId;
    }

    public function setHashId(string $hashId): self
    {
        $this->hashId = $hashId;

        return $this;
    }

    public function getTotalVotes(): ?int
    {
        return $this->totalVotes;
    }

    public function setTotalVotes(int $totalVotes): self
    {
        $this->totalVotes = $totalVotes;

        return $this;
    }

    public function getTotalActions(): ?int
    {
        return $this->totalActions;
    }

    public function setTotalActions(int $totalActions): self
    {
        $this->totalActions = $totalActions;

        return $this;
    }

    public function getAbsenceVotes(): ?int
    {
        return $this->absenceVotes;
    }

    public function setAbsenceVotes(int $absenceVotes): self
    {
        $this->absenceVotes = $absenceVotes;

        return $this;
    }

    public function getAbsenceActions(): ?int
    {
        return $this->absenceActions;
    }

    public function setAbsenceActions(int $absenceActions): self
    {
        $this->absenceActions = $absenceActions;

        return $this;
    }

    public function getActiveStatus(): ?array
    {
        return $this->activeStatus;
    }

    public function setActiveStatus(array $activeStatus): self
    {
        $this->activeStatus = $activeStatus;

        return $this;
    }

    /**
     * @return Collection|MeetingVoting[]
     */
    public function getMeetingVotings(): Collection
    {
        return $this->meetingVotings;
    }

    public function addMeetingVoting(MeetingVoting $meetingVoting): self
    {
        if (!$this->meetingVotings->contains($meetingVoting)) {
            $this->meetingVotings[] = $meetingVoting;
            $meetingVoting->setMeeting($this);
        }

        return $this;
    }

    public function removeMeetingVoting(MeetingVoting $meetingVoting): self
    {
        if ($this->meetingVotings->contains($meetingVoting)) {
            $this->meetingVotings->removeElement($meetingVoting);
            // set the owning side to null (unless already changed)
            if ($meetingVoting->getMeeting() === $this) {
                $meetingVoting->setMeeting(null);
            }
        }

        return $this;
    }

    public function getParticipantList(): ?ParticipantList
    {
        return $this->participantList;
    }

    public function setParticipantList(?ParticipantList $participantList): self
    {
        $this->participantList = $participantList;

        return $this;
    }

    public function getResultsForParticipants(): ?bool
    {
        return $this->resultsForParticipants;
    }

    public function setResultsForParticipants(bool $resultsForParticipants): self
    {
        $this->resultsForParticipants = $resultsForParticipants;

        return $this;
    }

    public function getKworum(): ?bool
    {
        return $this->kworum;
    }

    public function setKworum(bool $kworum): self
    {
        $this->kworum = $kworum;

        return $this;
    }

    public function getKworumRequiredValue(): ?int
    {
        return $this->kworumRequiredValue;
    }

    public function setKworumRequiredValue(?int $kworumValue): self
    {
        $this->kworumRequiredValue = $kworumValue;

        return $this;
    }

    public function getKworumType(): ?string
    {
        return $this->kworumType;
    }

    public function setKworumType(?string $kworumType): self
    {
        $this->kworumType = $kworumType;

        return $this;
    }

    public function getActiveVoting(): ?int
    {
        return $this->active_voting;
    }

    public function setActiveVoting(?int $active_voting): self
    {
        $this->active_voting = $active_voting;

        return $this;
    }

    public function getLastVoting(): ?int
    {
        return $this->last_voting;
    }

    public function setLastVoting(?int $last_voting): self
    {
        $this->last_voting = $last_voting;

        return $this;
    }

    public function getKworumValue(): ?float
    {
        return $this->kworumValue;
    }

    public function setKworumValue(?float $kworumValue): self
    {
        $this->kworumValue = $kworumValue;

        return $this;
    }

    public function getActiveStatusArray(): array
    {
        $array=$this->activeStatus;
        $array['active']=$this->active_voting;
        $array['last']=$this->last_voting;
        if(!isset($array['kworum']))
        {
            $array['kworum']=null;
        }
        return $array;
    }

    
}
