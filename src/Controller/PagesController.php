<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{_locale}")
 */
class PagesController extends AbstractController
{
    /**
     * @Route("/terms", name="app_terms")
     */
    public function termsPage(): Response
    {
        return $this->render('pages/terms.html.twig', [
            'terms' => 'Regulamin serwisu',
        ]);
    }

    /**
     * @Route("/privacy", name="app_privacy")
     */
    public function privacyPage(): Response
    {
        return $this->render('pages/privacy.html.twig', [
            'privacy' => 'Polityka prywatności',
        ]);
    }

    /**
     * @Route("/instruction", name="app_instruction")
     */
    public function instructionPage(): Response
    {
        return $this->render('pages/help.html.twig', [
            'help' => 'Tutaj będzie instrukcja',
        ]);
    }
}
