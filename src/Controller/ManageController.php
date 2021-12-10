<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventCode;
use App\Entity\Polling;
use App\Entity\Room;
use App\Entity\SessionSettings;
use App\Entity\User;
use App\Form\EventCodeType;
use App\Form\SessionSettingsType;
use App\Repository\EventRepository;
use App\Repository\PollingRepository;
use App\Repository\SessionSettingsRepository;
use App\Repository\SessionUsersRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/manage")
 */
class ManageController extends AbstractController
{
    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="app_manage")
     * @param Request $request
     * @param EventRepository $repository
     * @return Response
     */
    public function index(Request $request, EventRepository $repository): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        /** @var User $user */
        $user=$this->getUser();
        $events=$repository->getUserEvents($user);
        return $this->render('manage/index.html.twig', [
            'events' => $events
        ]);
    }

    /**
     * @Route("/event/{slug}", name="app_manage_event_show")
     * @param Event $event
     * @return Response
     */
    public function show(Event $event): Response
    {
        $user=$this->getUser();
        if($event->getUser()!==$user)
        {
            return $this->redirectToRoute('app_event_list');
        }

        return $this->render('event/show.html.twig',[
            'event'=>$event,
            'manage'=>true,
            'rooms'=>$event->getRooms()
        ]);
    }

    /**
     * @Route("/event/{slug_parent}/room/{slug_child}/show", name="app_manage_room")
     * @ParamConverter("event", options={"mapping": {"slug_parent": "slug"}})
     * @ParamConverter("room", options={"mapping": {"slug_child": "slug"}})
     * @param Event $event
     * @param Room $room
     * @return Response
     */
    public function manageRoom(Event $event,Room $room): Response
    {
        $user=$this->getUser();
        if($event->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $pollings=$room->getPollings();
        $general_meetings=$room->getGeneralMeetings();
        return $this->render('room/index.html.twig',[
           'pollings'=>$pollings,
           'meetings'=>$general_meetings,
           'manage'=>true,
            'room'=>$room
        ]);
    }

    /**
     * @Route("/polling/{slug}/show", name="app_manage_polling_show")
     * @param Polling $polling
     * @return Response
     */
    public function managePolling(Polling $polling): Response
    {
        $em=$this->getDoctrine()->getManager();
        $user=$this->getUser();
        if($polling->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $questions=$polling->getQuestions();
        $settings=$em->getRepository(SessionSettings::class)->getSessionSettings($polling);
        $polling->setSettings($settings);
        return $this->render('polling/index.html.twig',[
            'polling'=>$polling,
            'questions'=>$questions,
            'manage'=>true,
            'now'=>new \DateTime()
        ]);
    }

    /**
     * @Route("/event_code/{id}/edit", name="app_manage_code_edit")
     * @param EventCode $code
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editCode(EventCode $code,Request $request):Response
    {
        $user=$this->getUser();
        if($code->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $form=$this->createForm(EventCodeType::class,$code);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($code);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('codes.manage.edit.success'));
            return $this->redirectToRoute('app_event_codes_menage',['slug'=>$code->getEvent()->getSlug()]);
        }

        return $this->render('manage/code_edit.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/polling/{slug}/session_settings", name="app_manage_session_settings")
     * @param Polling $polling
     * @param Request $request
     * @param SessionSettingsRepository $repository
     * @return RedirectResponse|Response
     */
    public function sessionSettings(Polling $polling,Request $request,SessionSettingsRepository $repository)
    {
        $user=$this->getUser();
        if($polling->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $settings=$repository->getSessionSettings($polling);
        if(is_null($settings))
        {
            $settings= new SessionSettings();
            $settings->setPolling($polling);
        }

        $form=$this->createForm(SessionSettingsType::class,$settings);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            if($settings->getStatus()==0) {
                $begin = $form->get('begin')->getData();
                if ($begin) {
                    $settings
                        ->setStatus(1);
                }
            }
            if($settings->getStatus()===1)
            {
                $start = new \DateTime("+30 sec");
                $end = clone $start;
                $end->modify("+ {$settings->getTimeForAnswer()} seconds");
                $settings
                    ->setAnswerStart($start)
                    ->setAnswerEnd($end);
            }
            $em->persist($settings);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('session.settings.edit.success'));
            return $this->redirectToRoute('app_manage_polling_show',[
                //'slug_child'=>$polling->getRoom()->getSlug(),
                'slug'=>$polling->getSlug()
            ]);
        }

        return $this->render('manage/sessionSettings.html.twig',[
           'form'=>$form->createView(),
           'polling'=>$polling
        ]);
    }

    /**
     * @Route("/polling/{slug}/session_users", name="app_manage_session_users")
     * @param Polling $polling
     * @param SessionUsersRepository $repository
     * @return Response
     */
    public function sessionUsers(Polling $polling,SessionUsersRepository $repository): Response
    {
        $user=$this->getUser();
        if($polling->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $users=$repository->getPollingUsers($polling);
        return $this->render('manage/sessionUsers.html.twig',[
            'users'=>$users,
            'polling'=>$polling
        ]);
    }

    /**
     * @Route("/polling/{slug}/begin_session",name="app_manage_begin_session")
     * @param Polling $polling
     * @param SessionSettingsRepository $repository
     * @param Request $request
     * @return RedirectResponse
     */
    public function beginSession(Polling $polling,SessionSettingsRepository $repository,Request $request): RedirectResponse
    {
        $user=$this->getUser();
        if($polling->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        /**
         * @var $settings SessionSettings|null
         */
        $settings=$repository->getSessionSettings($polling);
        if(is_null($settings))
        {
            return $this->redirectToRoute('app_manage_session_settings',[
                'slug'=>$polling->getSlug()
            ]);
        }
        $start=new \DateTime("+ 30 sec");
        $end=clone $start;
        $end->modify("+ {$settings->getTimeForAnswer()} seconds");
        $settings
            ->setStatus(1)
            ->setAnswerStart($start)
            ->setAnswerEnd($end)
            ;

        $em=$this->getDoctrine()->getManager();
        $em->persist($settings);
        $em->flush();
        $this->addFlash('success',$this->translator->trans('session.begin.success'));
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/polling/{slug}/end_session", name="app_manage_end_session", methods={"PATCH"})
     * @param Polling $polling
     * @param Request $request
     * @param SessionSettingsRepository $repository
     * @return RedirectResponse
     */
    public function endSession(Polling $polling,Request $request,SessionSettingsRepository $repository): RedirectResponse
    {
        $user=$this->getUser();
        if($polling->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        if ($this->isCsrfTokenValid('patch'.$polling->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            /**
             * @var $settings SessionSettings
             */
            $settings=$repository->getSessionSettings($polling);
            $settings->setStatus(2);
            $entityManager->persist($settings);
            $entityManager->flush();
        }
        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/pollings", name="app_pollings")
     * @param PollingRepository $repository
     * @param SessionSettingsRepository $settingsRepository
     * @return RedirectResponse|Response
     */
    public function pollings(PollingRepository $repository,SessionSettingsRepository $settingsRepository):Response
    {
        /** @var User $user */
        $user=$this->getUser();
        $pollingsArray=$repository->findByUser($user);
        $pollings=array();
        foreach ($pollingsArray as $polling)
        {
            $settings=$settingsRepository->getSessionSettings($polling);
            $polling->setSettings($settings);
            $pollings[]=$polling;
        }

        return $this->render('manage/pollings.html.twig',[
            'pollings'=>$pollings,
            'manage'=>true
        ]);
    }
}
