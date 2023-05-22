<?php


namespace App\Service;


use App\Entity\Answer;
use App\Entity\Candidate;
use App\Entity\GeneralMeeting;
use App\Entity\MeetingVoting;
use App\Entity\Participant;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExcelCourseGenerator
{
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;
    private ParameterBagInterface $parameterBag;

    /**
     * ExcelCourseGenerator constructor.
     * @param ParameterBagInterface $parameterBag
     * @param TranslatorInterface $translator
     */
    public function __construct(ParameterBagInterface $parameterBag,TranslatorInterface $translator)
    {
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
    }

    private function getParameter(string $name)
    {
        return $this->parameterBag->get($name);
    }

    public function createExcel(GeneralMeeting $meeting): Spreadsheet
    {
        $excell=new Spreadsheet();
        $excell->getDefaultStyle()->getFont()->setName('Arial');
        $excell->getDefaultStyle()->getFont()->setSize(8);

        return $this->generateSheets($excell,$meeting);
        //$this->saveFile($excell);

    }

    private function generateSheets(Spreadsheet $excell,GeneralMeeting $meeting)
    {
        /**
         * @var int $key
         * @var MeetingVoting $voting
         */
        $count = 0;
        foreach ($meeting->getMeetingVotings() as $key => $voting)
        {
            if(!$voting->getSecret()){
                if($key ==0 or $count===0)
                {
                    $sheet=$excell->getActiveSheet();
                }else{
                    $sheet=new Worksheet();
                    $excell->addSheet($sheet);
                }
                $count++;
                $sheet->setTitle($this->translator->trans('voting').($key+1));
                $sheet->getDefaultColumnDimension()->setWidth(15);
                /*foreach(range('A','Z') as $columnID) {
                    $sheet->getColumnDimension($columnID)
                        ->setAutoSize(true);
                }*/
                $sheet=$this->printVotingTitle($sheet,$voting,$meeting->getHoldBack());
                $sheet=$this->printParticipantsVotes($sheet,$voting,$meeting->getParticipantList()->getAcceptedParticipants(),$meeting->getHoldBack());
            }
        }
        $excell->setActiveSheetIndex(0);

        return $excell;
    }

    private function saveFile($excell)
    {
        $writer= new Xlsx($excell);
        $writer->setOffice2003Compatibility(true);
        $file=$this->getParameter('excell_xlsx_path').'course_raport.xlsx';
        $writer->save($file);
    }

    private function printVotingTitle(Worksheet $sheet, MeetingVoting $voting,bool $holdBack)
    {
        $sheet
            ->mergeCells('A1:B1')
            ->setCellValue('A1',$voting->getContent());
        $number=3;
        if(sizeof($voting->getCandidates())>0)
        {

            /**
             * @var  $key
             * @var Candidate $candidate */
            foreach($voting->getCandidates() as $key=>$candidate)
            {
                $sheet
                    ->setCellValue('A'.$number,$key+1)
                    ->setCellValue('B'.$number,$candidate->getName())
                    ;
                $number++;
            }
            $size=sizeof($voting->getCandidates());
        }elseif(sizeof($voting->getAnswers())>0)
        {
            /**
             * @var Answer $answer
             */
            foreach($voting->getAnswers() as $key=>$answer)
            {
                $sheet
                    ->setCellValue('A'.$number,$key+1)
                    ->setCellValue('B'.$number,$answer->getContent())
                ;
                $number++;
            }
            $size=sizeof($voting->getAnswers());
        }else{
            $sheet
                ->setCellValue('A3',1)
                ->setCellValue('B3',$this->translator->trans('vote.on'))
                ->setCellValue('A4',2)
                ->setCellValue('B4',$this->translator->trans('vote.against'))
                ;
            $size=2;
            if($holdBack)
            {
                $sheet
                    ->setCellValue('A5',3)
                    ->setCellValue('B5',$this->translator->trans('vote.hold_off'))
                    ;
                $size=3;
            }
        }
        return $this->printVotesHeaders($sheet,$size);
    }

    private function printVotesHeaders(Worksheet $sheet,$votes_count)
    {
        $letters=['F','G','H','I','J','K','L','M','N','O','P','R','S','T','W'];
        $sheet
            ->setCellValue('D1',$this->translator->trans('nr'))
            ->setCellValue('E1',$this->translator->trans('full_name'))
            ;
        for($i=0;$i<$votes_count;$i++)
        {
            $sheet
                ->setCellValue($letters[$i].'1',$i+1)
                ;
        }
        $styleArray = [
            'font' => [
                'bold' => false,
            ],
            'alignment' => [
                'horizontal' => Alignment::VERTICAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('D1:'.$letters[$votes_count-1].'1')->applyFromArray($styleArray);

        for($i=1;$i<30;$i++){
            $sheet->getColumnDimensionByColumn($i)->setAutoSize(false);
            if($i==1){
                $width=5;
            }elseif($i==2)
            {
                $width=40;
            }elseif($i==4)
            {
                $width=10;
            }elseif($i==5){
                $width=30;
            }else{
                $width=5;
            }
            $sheet->getColumnDimensionByColumn($i)->setWidth($width);
        }

        return $sheet;
    }

    private function printParticipantsVotes(Worksheet $sheet, MeetingVoting $voting,$participants, ?bool $holdBack)
    {
        $letters=['F','G','H','I','J','K','L','M','N','O','P','R','S','T','W'];
        if(sizeof($voting->getCandidates())>0)
        {
            $answers=sizeof($voting->getCandidates());
        }elseif(sizeof($voting->getAnswers())>0)
        {
            $answers=sizeof($voting->getAnswers());
        }else{
            $answers=2;
            if($holdBack)
            {
                $answers=3;
            }
        }
        $number=2;
        /**
         * @var Participant $participant
         */
        foreach ($participants as $participant)
        {
            $sheet
                ->setCellValue('D'.$number,$participant->getAid())
                ->setCellValue('E'.$number,$participant->getName())
                ;
            $status = $voting->getVoteStatus();
            if($voting->getType()==2)
            {
                for($i=1;$i<=$answers;$i++)
                {
                    if(isset($status[$i]))
                    {
                        if(array_key_exists($participant->getAid(),$status[$i]))
                        {
                            $sheet=$this->colorCell($sheet,$letters[$i-1].$number);
                        }
                    }

                }
            }elseif($voting->getType() ==3)
            {
                for($i=1;$i<=$answers;$i++) {
                    if (isset($status[$i])) {
                        if (in_array($participant->getAid(), $status[$i])) {
                            $sheet=$this->colorCell($sheet,$letters[$i-1].$number);
                        }
                    }
                }
            }else{
                if(array_key_exists($participant->getAid(),$status))
                {
                    switch ($status[$participant->getAid()])
                    {
                        case 1:
                            $letter='F';
                            break;
                        case 0:
                            $letter='G';
                            break;
                        case 2:
                            $letter='H';
                            break;
                        default:
                            break;
                    }
                    $sheet=$this->colorCell($sheet,$letter.$number);
                }

            }
            $number++;
        }

        $styleArray = [
            'font' => [
                'bold' => false,
            ],
            'alignment' => [
                'horizontal' => Alignment::VERTICAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('D2:E'.(sizeof($participants)+1))->applyFromArray($styleArray);

        return $sheet;
    }

    private function colorCell(Worksheet $sheet,string $cell): Worksheet
    {
        $styleArray = [
            'font' => [
                'bold' => false,
            ],
            'alignment' => [
                'horizontal' => Alignment::VERTICAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'fill' => array(
                'type' => Fill::FILL_SOLID,
                'color' => array('rgb' => 'FF0000')
            ),
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet
            ->getStyle($cell)
            ->applyFromArray($styleArray)
        ;
        $sheet->setCellValue($cell,'X');
        return $sheet;
    }

}