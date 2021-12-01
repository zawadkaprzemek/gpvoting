<?php

namespace App\Controller;

use App\Entity\Polling;
use App\Entity\Question;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use App\Repository\QuestionTypeRepository;
use App\Repository\VoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class QuestionController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/polling/{slug}/question/{sort}", name="app_question_show")
     * @param Polling $polling
     * @param Question $question
     * @return Response
     */
    public function indexAction(Polling $polling,Question $question): Response
    {
        return $this->render('question/index.html.twig', [
            'question' => $question,
            'polling'=>$polling
        ]);
    }

    /**
     * @Route("/polling/{slug}/question/{sort}/edit", name="app_question_edit")
     * @param Polling $polling
     * @param Question $question
     * @param Request $request
     * @param QuestionTypeRepository $typeRepository
     * @return Response
     */
    public function editAction(Polling $polling,Question $question,Request $request,QuestionTypeRepository $typeRepository): Response
    {
        $form=$this->createForm(QuestionType::class,$question);

        $originalAnswers = new ArrayCollection();

        // Create an ArrayCollection of the current Tag objects in the database
        foreach ($question->getAnswers() as $answer) {
            $originalAnswers->add($answer);
        }
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            foreach ($originalAnswers as $originalAnswer) {
                if (false === $question->getAnswers()->contains($originalAnswer)) {
                    $em->remove($originalAnswer);
                }
            }
            $count=0;
            foreach ($question->getAnswers() as $answer)
            {
                if($answer->isValid())
                    $count++;
            }
            if($count==0)
            {
                $type=3;
            }elseif($count==1)
            {
                $type=1;
                $question->setMultiChoice(false);
            }else{
                $type=2;
                $question->setMultiChoice(true);
            }
            $qType=$typeRepository->find($type);
            $question->setQuestionType($qType);
            $em->persist($question);
            $em->flush();
            $this->addFlash('success', $this->translator->trans('changes_saved'));
            return $this->redirectToRoute('app_polling_show',['slug'=>$polling->getSlug()]);
        }

        return $this->render('question/form.html.twig',[
           'form'=>$form->createView(),
            'title'=>$this->translator->trans('question.edit.text')
        ]);
    }

    /**
     * @Route("/polling/{slug}/question/{id}/delete", name="app_question_delete", methods={"DELETE"})
     * @param Request $request
     * @param Question $question
     * @param QuestionRepository $repository
     * @return Response
     */
    public function deleteQuestion(Request $request, Question $question,QuestionRepository $repository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$question->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($question->getAnswers() as $answer)
            {
                $entityManager->remove($answer);
            }
            $otherQuestions=$repository->findQuestionsWithHiggerSort($question);
            foreach($otherQuestions as $otherQuestion)
            {
                $otherQuestion->setSort($otherQuestion->getSort()-1);
                $entityManager->persist($otherQuestion);
            }
            $entityManager->remove($question);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_polling_show',array('slug'=>$question->getPolling()->getSlug()));
    }

    /**
     * @Route("polling/{slug}/question/{sort}/results",name="app_question_results")
     * @param Polling $polling
     * @param Question $question
     * @param VoteRepository $repository
     * @return Response
     */
    public function questionResults(Polling $polling,Question $question,VoteRepository $repository): Response
    {
        $votes=$repository->getQuestionVotes($question);
        if(count($votes)==0)
        {
            return $this->render('question/noresults.html.twig',[
                'question'=>$question
            ]);
        }
        $results=[];
        $votesArr=[];
        foreach ($question->getAnswers() as $answer)
        {
            $results[$answer->getId()]=array(
                'content'=>$answer->getContent(),
                'valid'=>$answer->isValid(),
                'count'=>0
            );
        }
        $sum=0;
        foreach ($votes as $key=> $vote) {
            $votesArr[$key]=array(
                'id'=>$vote->getId(),
                'answers'=>array(),
                'answerTime'=>$vote->getAnswerTime(),
                'test'=>$vote->isTest(),
                'who'=>$vote->getWho()
            );
            foreach ($vote->getAnswers() as $answer)
            {
                $votesArr[$key]['answers'][]=$results[$answer]['content'];
                $results[$answer]['count']++;
                $sum++;
            }
        }
        foreach ($results as $key=>$result)
        {
            $results[$key]['percent']=round(($result['count']/$sum)*100,2);
        }

        return $this->render('question/results.html.twig',[
            'results'=>$results,
            'question'=>$question,
            'votes'=>$votesArr
        ]);
    }
}
