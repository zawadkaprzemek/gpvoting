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
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('gm_vote_color', [$this, 'voteColor']),
        ];
    }

    public function voteColor(int $value)
    {
        if(is_numeric($value))
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

    }
}
