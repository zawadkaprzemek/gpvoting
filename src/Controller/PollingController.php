<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Event;
use App\Entity\Polling;
use App\Entity\Question;
use App\Entity\Room;
use App\Form\PollingEnterType;
use App\Form\PollingType;
use App\Form\QuestionType;
use App\Repository\QuestionRepository;
use App\Repository\QuestionTypeRepository;
use DateTime;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class PollingController extends AbstractController
{

    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/manage/{slug_parent}/{slug_child}/create_polling", name="app_manage_create_polling")
     * @ParamConverter("event", options={"mapping": {"slug_parent": "slug"}})
     * @ParamConverter("room", options={"mapping": {"slug_child": "slug"}})
     * @param Event $event
     * @param Room $room
     * @param Request $request
     * @return Response
     */
    public function newPollingAction(Event $event,Room $room,Request $request): Response
    {
        $user=$this->getUser();
        if($event->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $polling = new Polling();
        $polling->setRoom($room);
        $form = $this->createForm(PollingType::class, $polling);
        $form->handleRequest($request);
        if ($form->isSubmitted()){
            if($polling->getStartDate()< new DateTime())
            {
                $form->get('startDate')->addError(new FormError($this->translator->trans('polling.error.cannot_begin_in_past')));
            }
            if(!$polling->getSession())
            {
                if($polling->getEndDate()<$polling->getStartDate())
                {
                    $form->get('endDate')->addError(new FormError($this->translator->trans('polling.error.date_end_must_be_greater_than_start')));
                }
            }
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                if ($polling->getSession()) {
                    $polling->setEndDate(null);
                }
                $em->persist($polling);
                $em->flush();
                $this->addFlash('success', $this->translator->trans('polling.new.success'));
                return $this->redirectToRoute('app_questions_add', ['slug' => $polling->getSlug()]);
            }
        }
        return $this->render('polling/form.html.twig',[
           'form'=>$form->createView(),
            'title'=>$this->translator->trans('polling.new.text')
        ]);
    }

    /**
     * @Route("/polling/{slug}", name="app_polling_show")
     * @param Polling $polling
     * @return Response
     */
    public function show(Polling $polling): Response
    {
        $em=$this->getDoctrine()->getManager();
        $settings=$em->getRepository('App:SessionSettings')->getSessionSettings($polling);
        $polling->setSettings($settings);
        return $this->render('polling/index.html.twig',[
            'polling'=>$polling,
            'now'=>new \DateTime()
        ]);
    }

    /**
     * @Route("/manage/polling/{slug}/edit", name="app_polling_edit")
     * @param Polling $polling
     * @param Request $request
     * @return Response
     */
    public function edit(Polling $polling,Request $request)
    {
        $user=$this->getUser();
        if($polling->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $form=$this->createForm(PollingType::class,$polling);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            if(!$polling->getSession())
            {
                if($polling->getEndDate()<$polling->getStartDate())
                {
                    $form->get('endDate')->addError(new FormError($this->translator->trans('polling.error.date_end_must_be_greater_than_start')));
                }
            }
            if($form->isValid())
            {
                $em=$this->getDoctrine()->getManager();
                if($polling->getSession())
                {
                    $polling->setEndDate(null);
                }
                $em->persist($polling);
                $em->flush();
                $this->addFlash('success', $this->translator->trans('changes_saved'));
                return $this->redirectToRoute('app_polling_show',['slug'=>$polling->getSlug()]);
            }
        }
        return $this->render('polling/form.html.twig',[
            'form'=>$form->createView(),
            'title'=>$this->translator->trans('polling.edit.text')
        ]);
    }

    /**
     * @Route("/manage/polling/{slug}/addQuestions", name="app_questions_add")
     * @param Polling $polling
     * @param Request $request
     * @param QuestionRepository $repository
     * @param QuestionTypeRepository $typeRepository
     * @return Response
     */
    public function addQuestions(Polling $polling,Request $request,QuestionRepository $repository,QuestionTypeRepository $typeRepository)
    {
        $user=$this->getUser();
        if($polling->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $question=new Question();
        $question->setPolling($polling);
        $sort=$repository->getSort($polling);
        if(sizeof($polling->getQuestions())==$polling->getQuestionsCount())
        {
            $this->addFlash('warning',$this->translator->trans('polling.error.limit_questions_reached'));
            return $this->redirectToRoute('app_polling_show',['slug'=>$polling->getSlug()]);
        }
        $question->setSort($sort['sort']+1);
        for($i=0;$i<$polling->getDefaultAnswers();$i++)
        {
            $answer=new Answer();
            $question->addAnswer($answer);
        }
        $form=$this->createForm(QuestionType::class,$question);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
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
            $em=$this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();
            $this->addFlash('success', $this->translator->trans('polling.new.question.success'));
            if(isset($form["next"]))
            {
                if($form->get('next')->getData())
                {
                    return $this->redirectToRoute('app_questions_add',['slug'=>$polling->getSlug()]);
                }else{
                    return $this->redirectToRoute('app_polling_show',['slug'=>$polling->getSlug()]);
                }
            }else{
                return $this->redirectToRoute('app_polling_show',['slug'=>$polling->getSlug()]);
            }
        }
        return $this->render('question/form.html.twig',[
            'form'=>$form->createView(),
            'title'=>$this->translator->trans('polling.new.question.text')
        ]);
    }



    /**
     * @Route("polling/{slug}/results",name="app_polling_results")
     * @param Polling $polling
     * @return RedirectResponse
     */
    public function resultsAction(Polling $polling): RedirectResponse
    {
        $user=$this->getUser();
        if($polling->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        return $this->redirectToRoute('app_question_results',[
           'slug'=>$polling->getSlug(),
           'sort'=>1
        ]);
    }

    /**
     * @Route("/polling/{id}/delete", name="app_polling_delete", methods={"DELETE"})
     * @param Request $request
     * @param Polling $polling
     * @return Response
     */
    public function delete(Request $request, Polling $polling): Response
    {
        $user=$this->getUser();
        if($polling->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        if ($this->isCsrfTokenValid('delete'.$polling->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($polling);
            $entityManager->flush();
            $this->addFlash('success',$this->translator->trans('polling.delete.success'));
        }
        return $this->redirectToRoute('app_manage_room',[
            'slug_parent'=>$polling->getRoom()->getEvent()->getSlug(),
            'slug_child'=>$polling->getRoom()->getSlug()
        ]);
    }

    /**
     * @Route("/polling/{slug}/enter", name="app_polling_enter")
     * @param Polling $polling
     * @param Request $request
     * @return Response
     */
    function enterPolling(Polling $polling,Request $request): Response
    {
        if(!$polling->getRoom()->getVisible())
        {
            return $this->redirectToRoute('home');
        }
        $session=$request->getSession();
        $enter=$session->get('polling_'.$polling->getId().'_code');
        if(!is_null($enter)||$polling->getSession())
        {
            return $this->redirectToRoute('app_polling_vote',[
                'slug'=>$polling->getSlug()
            ]);
        }
        $form=$this->createForm(PollingEnterType::class,null);
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            $code=$form->get('code')->getData();
            if($code!==$polling->getCode()->getName())
            {
                $form->get('code')->addError(new FormError($this->translator->trans('codes.bad_code_enter')));
            }
            if($form->isValid())
            {
                $session->set('polling_'.$polling->getId().'_code',$code);
                return $this->redirectToRoute('app_polling_vote',[
                    'slug'=>$polling->getSlug()
                ]);
            }
        }

        return $this->render('polling/enter.html.twig', [
            'form'=>$form->createView(),
            'polling'=>$polling
        ]);
    }

}
