<?php

namespace App\Controller;

use App\Entity\GeneralMeeting;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class PdfController extends AbstractController
{
    /**
     * @Route("{_locale}/pdf/general_meeting_results/{slug}/{valid}", name="general_meeting_results_pdf", requirements={"valid":"0|1"}, defaults={"valid":"0"})
     * @param Pdf $knpSnappyPdf
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @param int $valid
     * @return PdfResponse
     */
    public function generalMeetingResultsPdf(Pdf $knpSnappyPdf,GeneralMeeting $meeting,Request $request,int $valid)
    {
        $html = $this->renderView('pdf/general_meeting_results.html.twig', array(
            'meeting'  => $meeting,
            'valid'=>$valid,
            'base_dir' => $request->getScheme()."://".$request->server->get('HTTP_HOST')
        ));

        return new PdfResponse(
            $knpSnappyPdf->getOutputFromHtml($html),
            $meeting->getSlug().'.pdf'
        );
    }
}
