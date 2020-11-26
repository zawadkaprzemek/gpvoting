<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppCountValidVotesExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('valid_votes', [$this, 'validVotes']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('valid_votes', [$this, 'validVotes']),
        ];
    }

    public function validVotes($value,$valid)
    {
        $validCount=count(array_filter($value));
        if($valid==1)
        {
            return $validCount;
        }else{
            return count($value)-$validCount;
        }

    }
}
