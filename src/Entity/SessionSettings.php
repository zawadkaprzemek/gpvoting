<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\SessionSettingsRepository")
 */
class SessionSettings
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Polling", cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $polling;

    /**
     * @ORM\Column(type="integer")
     * 0 - oczekująca na uruchomienie
     * 1 - uruchomiona
     * 2 - zakończona
     */
    private $status=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $active_question=0;

    /**
     * @ORM\Column(type="integer")
     */
    private $timeForAnswer;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $answerStart;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $answerEnd;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPolling(): ?Polling
    {
        return $this->polling;
    }

    public function setPolling(Polling $polling): self
    {
        $this->polling = $polling;

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

    public function getActiveQuestion(): ?int
    {
        return $this->active_question;
    }

    public function setActiveQuestion(int $active_question): self
    {
        $this->active_question = $active_question;

        return $this;
    }

    public function getTimeForAnswer(): ?int
    {
        return $this->timeForAnswer;
    }

    public function setTimeForAnswer(int $timeForAnswer): self
    {
        $this->timeForAnswer = $timeForAnswer;

        return $this;
    }

    public function getAnswerStart(): ?\DateTimeInterface
    {
        return $this->answerStart;
    }

    public function setAnswerStart(?\DateTimeInterface $answerStart): self
    {
        $this->answerStart = $answerStart;

        return $this;
    }

    public function getAnswerEnd(): ?\DateTimeInterface
    {
        return $this->answerEnd;
    }

    public function setAnswerEnd(?\DateTimeInterface $answerEnd): self
    {
        $this->answerEnd = $answerEnd;

        return $this;
    }
}
