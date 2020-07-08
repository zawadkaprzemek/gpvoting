<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PollingRepository")
 */
class Polling
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Room", inversedBy="pollings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $room;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="integer")
     */
    private $questionsCount;

    /**
     * @ORM\Column(type="integer")
     */
    private $default_answers;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Question", mappedBy="polling")
     */
    private $questions;

    /**
     * @ORM\Column(type="boolean")
     */
    private $open=true;

    /**
     * @ORM\Column(type="boolean")
     */
    private $session;

    /**
     * @ORM\Column(type="integer")
     * 1 - wykres pionowy
     * 2 - wykres poziomy
     */
    private $resultsGraph;

    /**
     * @ORM\Column(type="datetime")
     */
    private $startDate;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\EventCode", inversedBy="pollings")
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SessionUsers", mappedBy="polling")
     */
    private $sessionUsers;

    public function __construct()
    {
        $this->questions = new ArrayCollection();
        $this->sessionUsers = new ArrayCollection();
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getQuestionsCount(): ?int
    {
        return $this->questionsCount;
    }

    public function setQuestionsCount(int $questionsCount): self
    {
        $this->questionsCount = $questionsCount;

        return $this;
    }

    public function getDefaultAnswers(): ?int
    {
        return $this->default_answers;
    }

    public function setDefaultAnswers(int $default_answers): self
    {
        $this->default_answers = $default_answers;

        return $this;
    }


    /**
     * @return Collection|Question[]
     */
    public function getQuestions(): Collection
    {
        return $this->questions;
    }

    public function addQuestion(Question $question): self
    {
        if (!$this->questions->contains($question)) {
            $this->questions[] = $question;
            $question->setPolling($this);
        }

        return $this;
    }

    public function removeQuestion(Question $question): self
    {
        if ($this->questions->contains($question)) {
            $this->questions->removeElement($question);
            // set the owning side to null (unless already changed)
            if ($question->getPolling() === $this) {
                $question->setPolling(null);
            }
        }

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

    public function getSession(): ?bool
    {
        return $this->session;
    }

    public function setSession(bool $session): self
    {
        $this->session = $session;

        return $this;
    }

    public function getResultsGraph(): ?int
    {
        return $this->resultsGraph;
    }

    public function setResultsGraph(int $resultsGraph): self
    {
        $this->resultsGraph = $resultsGraph;

        return $this;
    }

    /**
     * @param mixed $room
     * @return Polling
     */
    public function setRoom($room)
    {
        $this->room = $room;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRoom()
    {
        return $this->room;
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

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): self
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getCode(): ?EventCode
    {
        return $this->code;
    }

    public function setCode(?EventCode $code): self
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection|SessionUsers[]
     */
    public function getSessionUsers(): Collection
    {
        return $this->sessionUsers;
    }

    public function addSessionUser(SessionUsers $sessionUser): self
    {
        if (!$this->sessionUsers->contains($sessionUser)) {
            $this->sessionUsers[] = $sessionUser;
            $sessionUser->setPolling($this);
        }

        return $this;
    }

    public function removeSessionUser(SessionUsers $sessionUser): self
    {
        if ($this->sessionUsers->contains($sessionUser)) {
            $this->sessionUsers->removeElement($sessionUser);
            // set the owning side to null (unless already changed)
            if ($sessionUser->getPolling() === $this) {
                $sessionUser->setPolling(null);
            }
        }

        return $this;
    }
}
