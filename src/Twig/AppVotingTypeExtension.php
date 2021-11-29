<?php

namespace App\Twig;

use App\Entity\MeetingVoting;
use Gedmo\Translator\TranslationInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppVotingTypeExtension extends AbstractExtension
{
    /**
     * @var TranslationInterface
     */
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator=$translator;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('voting_type', [$this, 'voting_type']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('voting_type', [$this, 'voting_type']),
        ];
    }

    public function voting_type($value,bool $withNumbers = true)
    {
        /** @var $value MeetingVoting */
        switch ($value->getType())
        {
            case 1:
                $type=$this->translator->trans('general_meeting.voting.resolution');
                break;
            case 2:
                $type=$this->translator->trans('general_meeting.voting.personal');
                if($withNumbers) {
                    $type.=" (" . sizeof($value->getCandidates()) . ")";
                }
                break;
            case 3:
                $type=$this->translator->trans('general_meeting.voting.poll');
                if($withNumbers)
                {
                    $type.=" (".sizeof($value->getAnswers()).")";
                }
                break;
            default:
                $type="";
                break;
        }

        return $type;
    }
}
