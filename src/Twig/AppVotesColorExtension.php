<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppVotesColorExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('gm_vote_color', [$this, 'voteColor']),
            new TwigFilter('course_votes', [$this, 'courseVotes']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('gm_vote_color', [$this, 'voteColor']),
            new TwigFunction('course_votes', [$this, 'courseVotes']),
        ];
    }

    public function voteColor(int $value): string
    {
        $bg="bg-";
        if($value===1)
        {
            $bg .= "success";
        }elseif ($value===0)
        {
            $bg .= "danger";
        }elseif ($value===2)
        {
            $bg .= "warning";
        }else{
            $bg .= "light";
        }
        return $bg;
    }

    public function courseVotes(int $vote,bool $holdOn): string
    {
        $html='';
        switch ($vote)
        {
            case 1:
                $html='<td class="course-box bg-dark">&nbsp;</td><td class="course-box bg-light">&nbsp;</td>';
                if($holdOn)
                {
                    $html .= '<td class="course-box bg-light">&nbsp;</td>';
                }
                break;
            case 0:
                $html='<td class="course-box bg-light">&nbsp;</td><td class="course-box bg-dark">&nbsp;</td>';
                if($holdOn)
                {
                    $html .= '<td class="course-box bg-light">&nbsp;</td>';
                }
                break;
            case 2:
                $html='<td class="course-box bg-light">&nbsp;</td><td class="course-box bg-light">&nbsp;</td>';
                if($holdOn)
                {
                    $html .= '<td class="course-box bg-dark">&nbsp;</td>';
                }
                break;
                default:
                    break;

        }
        return $html;
    }
}
