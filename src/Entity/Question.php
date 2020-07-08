<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\QuestionRepository")
 */
class Question
{
    use TimestampableEntity;
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Polling", inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $polling;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $question_content;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\QuestionType", inversedBy="questions")
     * @ORM\JoinColumn(nullable=false)
     */
    private $questionType;

    /**
     * @ORM\Column(type="boolean")
     */
    private $multiChoice=false;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Answer",cascade={"persist"}, mappedBy="question")
     */
    private $answers;

    /**
     * @ORM\Column(type="integer")
     */
    private $sort;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Vote", mappedBy="question")
     */
    private $votes;

    public function __construct()
    {
        $this->answers = new ArrayCollection();
        $this->votes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPolling(): ?Polling
    {
        return $this->polling;
    }

    public function setPolling(?Polling $polling): self
    {
        $this->polling = $polling;

        return $this;
    }

    public function getQuestionContent(): ?string
    {
        return $this->question_content;
    }

    public function setQuestionContent(string $question_content): self
    {
        $this->question_content = $question_content;

        return $this;
    }

    public function getQuestionType(): ?QuestionType
    {
        return $this->questionType;
    }

    public function setQuestionType(?QuestionType $questionType): self
    {
        $this->questionType = $questionType;

        return $this;
    }

    public function getMultiChoice(): ?bool
    {
        return $this->multiChoice;
    }

    public function setMultiChoice(bool $multiChoice): self
    {
        $this->multiChoice = $multiChoice;

        return $this;
    }

    /**
     * @return Collection|Answer[]
     */
    public function getAnswers(): Collection
    {
        return $this->answers;
    }

    public function addAnswer(Answer $answer): self
    {
        if (!$this->answers->contains($answer)) {
            $this->answers[] = $answer;
            $answer->setQuestion($this);
        }

        return $this;
    }

    public function removeAnswer(Answer $answer): self
    {
        if ($this->answers->contains($answer)) {
            $this->answers->removeElement($answer);
            // set the owning side to null (unless already changed)
            if ($answer->getQuestion() === $this) {
                $answer->setQuestion(null);
            }
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

    /**
     * @return Collection|Vote[]
     */
    public function getVotes(): Collection
    {
        return $this->votes;
    }

    public function addVote(Vote $vote): self
    {
        if (!$this->votes->contains($vote)) {
            $this->votes[] = $vote;
            $vote->setQuestion($this);
        }

        return $this;
    }

    public function removeVote(Vote $vote): self
    {
        if ($this->votes->contains($vote)) {
            $this->votes->removeElement($vote);
            // set the owning side to null (unless already changed)
            if ($vote->getQuestion() === $this) {
                $vote->setQuestion(null);
            }
        }

        return $this;
    }
}
