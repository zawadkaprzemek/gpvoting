<?php

namespace App\Twig;

use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class IconsExtension extends AbstractExtension
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('help_icon', [$this, 'help_icon'], ['is_safe' => ['html']]),
            new TwigFilter('info_icon', [$this, 'info_icon'], ['is_safe' => ['html']]),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('help_icon', [$this, 'help_icon']),
            new TwigFunction('info_icon', [$this, 'info_icon']),
        ];
    }

    public function info_icon($value): string
    {
        $type='info';
        $trans=$this->getTransition($value,$type);
        if($trans!==$value.'.'.$type) {
            return $this->generateIcon($trans,'info');
        }
        return '';
    }

    public function help_icon($value): string
    {
        $type='help';
        $trans=$this->getTransition($value,$type);
        if($trans!==$value.'.'.$type)
        {
            return $this->generateIcon($trans,'question');
        }
        return '';
    }

    private function generateIcon($trans,$type): string
    {
        return '<span title="'.$trans.'" data-toggle="tooltip" data-placement="top"><i class="fas fa-'.$type.'-circle"></i></span>';
    }

    private function getTransition($value,$type): string
    {
        return $trans=$this->translator->trans($value.'.'.$type);
    }

}
