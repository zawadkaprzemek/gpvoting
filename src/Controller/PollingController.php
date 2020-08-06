<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Candidate;
use App\Entity\Event;
use App\Entity\GeneralMeeting;
use App\Entity\Polling;
use App\Entity\Question;
use App\Entity\Resolution;
use App\Entity\Room;
use App\Form\GeneralMeetingCandidatesType;
use App\Form\GeneralMeetingResolutionsType;
use App\Form\GeneralMeetingType;
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
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/polling", name="polling")
     */
    public function index()
    {
        return $this->render('polling/index.html.twig', [
            'controller_name' => 'PollingController',
        ]);
    }

    /**
     * @Route("/{_locale}/manage/{slug_parent}/{slug_child}/create_polling", name="app_manage_create_polling")
     * @ParamConverter("event", options={"mapping": {"slug_parent": "slug"}})
     * @ParamConverter("room", options={"mapping": {"slug_child": "slug"}})
     * @param Event $event
     * @param Room $room
     * @param Request $request
     * @return Response
     */
    public function new(Event $event,Room $room,Request $request)
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
                $form->get('startDate')->addError(new FormError('Głosowanie nie może zaczynać się w przeszłości'));
            }
            if(!$polling->getSession())
            {
                if($polling->getEndDate()<$polling->getStartDate())
                {
                    $form->get('endDate')->addError(new FormError('Data końcowa nie może być wcześniejsza od daty startowej'));
                }
            }
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                if ($polling->getSession()) {
                    $polling->setEndDate(null);
                }
                $em->persist($polling);
                $em->flush();
                $this->addFlash('success', 'Dodano nowe głosowanie');
                return $this->redirectToRoute('app_questions_add', ['slug' => $polling->getSlug()]);
            }
        }
        return $this->render('polling/form.html.twig',[
           'form'=>$form->createView(),
            'title'=>$this->translator->trans('Dodaj nowe głosowanie')
        ]);
    }

    /**
     * @Route("/{_locale}/polling/{slug}", name="app_polling_show")
     * @param Polling $polling
     * @return Response
     */
    public function show(Polling $polling)
    {
        return $this->render('polling/index.html.twig',[
            'polling'=>$polling
        ]);
    }

    /**
     * @Route("/{_locale}/manage/polling/{slug}/edit", name="app_polling_edit")
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
                    $form->get('endDate')->addError(new FormError('Data końcowa nie może być wcześniejsza od daty startowej'));
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
                $this->addFlash('success', 'Zapisano zmiany');
                return $this->redirectToRoute('app_polling_show',['slug'=>$polling->getSlug()]);
            }
        }
        return $this->render('polling/form.html.twig',[
            'form'=>$form->createView(),
            'title'=>$this->translator->trans('Edytuj głosowanie')
        ]);
    }

    /**
     * @Route("/{_locale}/manage/polling/{slug}/addQuestions", name="app_questions_add")
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
            $this->addFlash('warning','Osiągnięto limit pytań dla tego głosowania');
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
            $this->addFlash('success', 'Dodano nowe pytanie');
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
            'title'=>$this->translator->trans('Dodaj nowe pytanie')
        ]);
    }



    /**
     * @Route("{_locale}/polling/{slug}/results",name="app_polling_results")
     * @param Polling $polling
     * @return RedirectResponse
     */
    public function results(Polling $polling)
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
     * @Route("/{_locale}/polling/{id}/delete", name="app_polling_delete", methods={"DELETE"})
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
            $this->addFlash('success','Usunięto głosowanie');
        }
        return $this->redirectToRoute('app_manage_room',[
            'slug_parent'=>$polling->getRoom()->getEvent()->getSlug(),
            'slug_child'=>$polling->getRoom()->getSlug()
        ]);
    }

    /**
     * @Route("/{_locale}/polling/{slug}/enter", name="app_polling_enter")
     * @param Polling $polling
     * @param Request $request
     * @return Response
     */
    function enterPolling(Polling $polling,Request $request)
    {
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
                $form->get('code')->addError(new FormError('Nie poprawny kod'));
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

    /**
     * @Route("/{_locale}/manage/{slug_parent}/{slug_child}/create_general_meeting", name="app_manage_create_general_meeting")
     * @ParamConverter("event", options={"mapping": {"slug_parent": "slug"}})
     * @ParamConverter("room", options={"mapping": {"slug_child": "slug"}})
     * @param Event $event
     * @param Room $room
     * @param Request $request
     * @return Response
     */
    public function createGeneralMeeting(Event $event,Room $room,Request $request)
    {
        $meeting=new GeneralMeeting();
        $meeting->setRoom($room);
        $form=$this->createForm(GeneralMeetingType::class,$meeting);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            if($meeting->getVariant()===1)
            {
                $meeting->setCount($form->get('countResolution')->getData());
            }elseif ($meeting->getVariant()===2)
            {
                $meeting->setCount($form->get('countPersonal')->getData());
            }

            $em->persist($meeting);
            $em->flush();
            $this->addFlash('succes',$this->translator->trans('Dodano nowe Walne zgromadzenie'));
            if($meeting->getVariant()===1)
            {
                return $this->redirectToRoute('app_manage_general_meeting_add_resolutins',['slug'=>$meeting->getSlug()]);
            }elseif($meeting->getVariant()===2)
            {
                return $this->redirectToRoute('app_manage_general_meeting_add_candidates',['slug'=>$meeting->getSlug()]);
            }
        }

        return $this->render('polling/general_meeting_form.html.twig',[
            'title'=>$this->translator->trans('Utwórz walne zgromadzenie'),
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/addResolutions",name="app_manage_general_meeting_add_resolutins")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addResolutions(GeneralMeeting $meeting,Request $request)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        for($i=0;$i<$meeting->getCount();$i++)
        {
            $meeting->addResolution(new Resolution());
        }

        $form=$this->createForm(GeneralMeetingResolutionsType::class,$meeting);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($meeting);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('Dodano uchwały!'));
            return $this->redirectToRoute('app_manage_general_meeting_show',['slug'=>$meeting->getSlug()]);
        }

        return $this->render('polling/resolutions_form.html.twig',[
           'form'=>$form->createView(),
           'meeting'=>$meeting
        ]);
    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/addCandidates",name="app_manage_general_meeting_add_candidates")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addCandidates(GeneralMeeting $meeting,Request $request)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        for($i=0;$i<$meeting->getCount();$i++)
        {
            $meeting->addCandidate(new Candidate());
        }
        $form=$this->createForm(GeneralMeetingCandidatesType::class,$meeting);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($meeting);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('Dodano kandydatów'));
            return $this->redirectToRoute('app_manage_general_meeting_show',[
                'slug'=>$meeting->getSlug()
            ]);
        }

        return $this->render('polling/candidates_form.html.twig',[
           'form'=>$form->createView(),
           'meeting'=>$meeting
        ]);
    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}", name="app_manage_general_meeting_show")
     * @param GeneralMeeting $meeting
     * @return RedirectResponse|Response
     */
    public function showGeneralMeeting(GeneralMeeting $meeting)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }

        return $this->render('polling/general_meeting_show.html.twig',[
           'meeting'=>$meeting,
           'manage'=>true
        ]);
    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/resolutions", name="app_manage_general_meeting_resolutions_list")
     * @param GeneralMeeting $meeting
     * @return RedirectResponse|Response
     */
    public function resolutionsList(GeneralMeeting $meeting)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        if($meeting->getVariant()==2)
        {
            return $this->redirectToRoute('app_manage_general_meeting_candidates_list',['slug'=>$meeting->getSlug()]);
        }

        $resolutions=$meeting->getResolutions();
        return $this->render('polling/resolutions_list.html.twig',[
            'resolutions'=>$resolutions,
            'meeting'=>$meeting
        ]);

    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/candidates", name="app_manage_general_meeting_candidates_list")
     * @param GeneralMeeting $meeting
     * @return Response
     */
    public function candidatesList(GeneralMeeting $meeting)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        if($meeting->getVariant()==1)
        {
            return $this->redirectToRoute('app_manage_general_meeting_resolutions_list',['slug'=>$meeting->getSlug()]);
        }

        $candidates=$meeting->getCandidates();
        return $this->render('polling/candidates_list.html.twig',[
            'candidates'=>$candidates,
            'meeting'=>$meeting
        ]);
    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/edit_candidates", name="app_manage_general_meeting_edit_candidates")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return Response
     */
    public function editCandidates(GeneralMeeting $meeting,Request $request)
    {
        if($meeting->getCount()!==sizeof($meeting->getCandidates()))
        {
            $diff=$meeting->getCount()-sizeof($meeting->getCandidates());
            for($i=0;$i<$diff;$i++)
            {
                $meeting->addCandidate(new Candidate());
            }
        }
        $form=$this->createForm(GeneralMeetingCandidatesType::class,$meeting);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($meeting);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('Edycja listy kandydatów zakończona sukcesem'));
            return $this->redirectToRoute('app_manage_general_meeting_candidates_list',['slug'=>$meeting->getSlug()]);
        }

        return $this->render('polling/candidates_form.html.twig',[
            'form'=>$form->createView(),
            'meeting'=>$meeting
        ]);
    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/edit_resolutions", name="app_manage_general_meeting_edit_resolutions")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return Response
     */
    public function editResolutions(GeneralMeeting $meeting,Request $request)
    {
        if($meeting->getCount()!==sizeof($meeting->getResolutions()))
        {
            $diff=$meeting->getCount()-sizeof($meeting->getResolutions());
            for($i=0;$i<$diff;$i++)
            {
                $meeting->addResolution(new Resolution());
            }
        }
        $form=$this->createForm(GeneralMeetingResolutionsType::class,$meeting);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($meeting);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('Edycja listy uchwał zakończona sukcesem'));
            return $this->redirectToRoute('app_manage_general_meeting_resolutions_list',['slug'=>$meeting->getSlug()]);
        }

        return $this->render('polling/resolutions_form.html.twig',[
            'form'=>$form->createView(),
            'meeting'=>$meeting
        ]);
    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/edit", name="app_manage_general_meeting_edit")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editGeneralMeeting(GeneralMeeting $meeting,Request $request)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }

        $oldCount=$meeting->getCount();
        $form=$this->createForm(GeneralMeetingType::class,$meeting);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            if($meeting->getVariant()===1)
            {
                $meeting->setCount($form->get('countResolution')->getData());
            }elseif ($meeting->getVariant()===2)
            {
                $meeting->setCount($form->get('countPersonal')->getData());
            }

            $em->persist($meeting);
            $em->flush();
            $this->addFlash('succes',$this->translator->trans('Edycja Walnego zgromadzenia zakończona powodzeniem'));
            if($meeting->getVariant()===1)
            {
                if($meeting->getCount()!=$oldCount)
                {
                    return $this->redirectToRoute('app_manage_general_meeting_edit_resolutions',['slug'=>$meeting->getSlug()]);
                }else{
                    return  $this->redirectToRoute('app_manage_general_meeting_show',['slug'=>$meeting->getSlug()]);
                }
            }elseif($meeting->getVariant()===2)
            {
                if($meeting->getCount()!=$oldCount)
                {
                    return $this->redirectToRoute('app_manage_general_meeting_edit_candidates',['slug'=>$meeting->getSlug()]);
                }else{
                    return  $this->redirectToRoute('app_manage_general_meeting_show',['slug'=>$meeting->getSlug()]);
                }
            }
        }

        return $this->render('polling/general_meeting_form.html.twig',[
            'form'=>$form->createView(),
            'title'=>$this->translator->trans('Edytuj Walne zgromadzenie'),
            'meeting'=>$meeting
        ]);
    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{id}/begin", name="app_manage_general_meeting_begin")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return RedirectResponse
     */
    public function openGeneralMeeting(GeneralMeeting $meeting,Request $request)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        if($meeting->getStatus()!==0)
        {
            $this->addFlash('danger',$this->translator->trans("Nie można ponownie rozpocząć tego zgromadzenia"));
            return $this->redirect($request->server->all()['HTTP_REFERER']);
        }

        $meeting->setStatus(1);
        $em=$this->getDoctrine()->getManager();
        $em->persist($meeting);
        $em->flush();
        $this->addFlash('success',$this->translator->trans('Walne zgromadzenie zostało rozpoczęte'));
        return $this->redirectToRoute('app_manage_general_meeting_cockpit',['slug'=>$meeting->getSlug()]);

    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{id}/end", name="app_manage_general_meeting_end")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return RedirectResponse
     */
    public function endGeneralMeeting(GeneralMeeting $meeting,Request $request)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        if($meeting->getStatus()!==1)
        {
            $this->addFlash('danger',$this->translator->trans("Nie można zakończyć tego zgromadzenia"));
            return $this->redirect($request->server->all()['HTTP_REFERER']);
        }

        $meeting->setStatus(2);
        $em=$this->getDoctrine()->getManager();
        $em->persist($meeting);
        $em->flush();
        $this->addFlash('success',$this->translator->trans('Walne zgromadzenie zostało zakońćzone'));
        return $this->redirectToRoute('app_manage_general_meeting_cockpit',['slug'=>$meeting->getSlug()]);

    }


    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/cockpit", name="app_manage_general_meeting_cockpit")
     * @param GeneralMeeting $meeting
     * @return RedirectResponse|Response
     */
    public function generalMeetingCockpit(GeneralMeeting $meeting)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }

        return $this->render('polling/cockpit.html.twig',[
           'meeting'=>$meeting
        ]);
    }


    /**
     * @Route("/{_locale}/general_meeting/{slug}/join", name="app_general_meeting_join")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function generalMeetingJoin(GeneralMeeting $meeting,Request $request)
    {
        $session=$request->getSession();
        if($meeting->getStatus()!==1)
        {
            $this->addFlash('warning',$this->translator->trans("Nie można przystąpić do tego zgromadzenia"));
            return $this->redirectToRoute('home');
        }

        $uData=$session->get("user_gm_".$meeting->getSlug());
        if(!is_null($uData))
        {
            // TO DO: Przekierowanie do głosowania
        }

        // TO DO: Formularz logowania do zgromadzenia

        return $this->render('polling/general_meeting_login.html.twig',[
           //'form'=>
        ]);
    }

}
