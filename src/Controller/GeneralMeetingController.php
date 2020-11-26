<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\Event;
use App\Entity\GeneralMeeting;
use App\Entity\Resolution;
use App\Entity\Room;
use App\Form\GeneralMeetingCandidatesType;
use App\Form\GeneralMeetingJoinType;
use App\Form\GeneralMeetingResolutionsType;
use App\Form\GeneralMeetingType;
use App\Repository\ParticipantRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GeneralMeetingController extends AbstractController
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
            $meeting->setHashId(uniqid());
            $em->persist($meeting);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('Dodano nowe Walne zgromadzenie'));
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

        if($meeting->getStatus()!=0)
        {
            $this->addFlash('warning',$this->translator->trans("Nie można edytować rozpoczętego zgromadzenia!"));
            return $this->redirect($request->server->get('HTTP_REFERER'));
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
     * @Route("/{_locale}/manage/general_meeting/{id}/begin", name="app_manage_general_meeting_begin", methods={"PATCH"})
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return JsonResponse|RedirectResponse
     */
    public function openGeneralMeeting(GeneralMeeting $meeting,Request $request)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        if($meeting->getStatus()!==0)
        {
            return new JsonResponse(array('status'=>'error'));
        }

        $meeting->setStatus(1);
        $active=array('active'=>null,'votes'=>array(),'last'=>null);
        if($meeting->getVariant()==2)
        {
            $active['title']=null;
            $active['turn']=null;
            $active['vote']=[];
            $active['voted']=[];
        }
        $meeting->setActiveStatus($active);
        $em=$this->getDoctrine()->getManager();
        $em->persist($meeting);
        $em->flush();
        return new JsonResponse(array('status'=>'success'));

    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/startTestVote", name="app_manage_general_meeting_test_vote_start", methods={"PATCH"})
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return JsonResponse
     */
    public function generalMeetingTestVotingStart(GeneralMeeting $meeting,Request $request): JsonResponse
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return new JsonResponse(array('status'=>'error'));
        }
        $active=$meeting->getActiveStatus();
        $active['active']=0;
        $active["votes"][0]=array();
        $meeting->setActiveStatus($active)
            ->setTotalActions(0)
            ->setTotalVotes(0)
            ->setAbsenceVotes(0)
            ->setAbsenceActions(0);
        $em=$this->getDoctrine()->getManager();
        $em->persist($meeting);
        $em->flush();
        return new JsonResponse(array('status'=>'success'));
    }

    /**
     * @Route("/{_locale}/general_meeting/{slug}/vote_save", name="app_ajax_general_meeting_save",methods={"PATCH"})
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return JsonResponse
     */
    public function generalMeetingVoteSave(GeneralMeeting $meeting,Request $request)
    {
        $data=json_decode($request->getContent());
        $active=$meeting->getActiveStatus();
        if($meeting->getVariant()==1)
        {
            $active['votes'][$active['active']][$data->user]=$data->vote;
        }else{
            if($active['active']==0)
            {
                $active['votes'][$active['active']][$data->user]=$data->vote;
            }else{
                foreach ($data->votes as $vote)
                {
                    $active['votes'][$active['turn']][$vote][$data->user]=array('valid',$data->valid,'votes'=>$data->p_votes/sizeof($data->votes),'actions'=>$data->p_actions/sizeof($data->votes));
                }
                $active['vote'][]=$data->user;
                $active['voted'][]=$data->user;
                $active['vote']=array_unique($active['vote']);
                $active['voted']=array_unique($active['voted']);
            }

        }

        $meeting->setActiveStatus($active);
        $em=$this->getDoctrine()->getManager();
        $em->persist($meeting);
        $em->flush();
        return new JsonResponse(array('status'=>'success'));
    }

    /**
     * @Route("/{_locale}/general_meeting/{slug}/end_vote", name="app_ajax_general_meeting_end_vote", methods={"PATCH"})
     * @param GeneralMeeting $meeting
     * @return JsonResponse
     */
    public function generalMeetingEndVote(GeneralMeeting $meeting)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return new JsonResponse(array('status'=>'error'));
        }
        $em=$this->getDoctrine()->getManager();
        $active=$meeting->getActiveStatus();
        if($active['active']==0)
        {
            $tot_a=0;
            $tot_v=0;
            $abs_a=0;
            $abs_v=0;
            $participants=$meeting->getParticipantList()->getParticipants();
            foreach ($participants as $participant)
            {
                if(array_key_exists($participant->getAid(),$active['votes'][0]))
                {
                    $tot_a=$tot_a+$participant->getActions();
                    $tot_v=$tot_v+$participant->getVotes();
                }else{
                    $abs_a=$abs_a+$participant->getActions();
                    $abs_v=$abs_v+$participant->getVotes();
                }

            }
            $meeting
                ->setTotalActions($tot_a)->setTotalVotes($tot_v)
                ->setAbsenceActions($abs_a)->setAbsenceVotes($abs_v)
            ;
        }
        if($meeting->getVariant()==1)
        {
            if($active['active']>0)
            {
                $curr=$active['active'];
                $on=array('actions'=>0,'votes'=>0);
                $against=array('actions'=>0,'votes'=>0);
                $hold=array('actions'=>0,'votes'=>0);
                $participants=$meeting->getParticipantList()->getParticipants();
                foreach ($participants as $participant)
                {
                    if(array_key_exists($participant->getAid(),$active['votes'][$curr]))
                    {
                        switch ($active['votes'][$curr][$participant->getAid()])
                        {
                            case 1:
                                $on['actions']=$on['actions']+$participant->getActions();
                                $on['votes']=$on['votes']+$participant->getVotes();
                                break;
                            case 0:
                                $against['actions']=$against['actions']+$participant->getActions();
                                $against['votes']=$against['votes']+$participant->getVotes();
                                break;
                            case 2:
                                $hold['actions']=$hold['actions']+$participant->getActions();
                                $hold['votes']=$hold['votes']+$participant->getVotes();
                                break;
                            default:
                                break;
                        }
                    }

                }

                if($meeting->getWeight()==1)
                {
                    $accepted=$on['votes']>$against['votes']&&$on['votes']>$hold['votes'];
                }else{
                    $accepted=$on['actions']>$against['actions']&&$on['actions']>$hold['actions'];
                }

                /** @var Resolution $resolution */
                $resolution=$meeting->getResolutions()->get($curr-1);
                $resolution
                    ->setVotesOnCount($on)
                    ->setVotesAgainstCount($against)
                    ->setVotesHoldCount($hold)
                    ->setAccepted($accepted)
                ;

                $em->persist($resolution);
            }
        }else{
            if($active['turn']>0&&$active['active']!=0)
            {
                $curr=$active['active'];
                $participants=$meeting->getParticipantList()->getParticipants();
                $candidates=$meeting->getCandidates();
                $resultArray=array();
                foreach ($active['votes'][$active['turn']] as $key=>$row)
                {
                    $actions=0;
                    $votes=0;
                    foreach ($participants as $participant)
                    {
                        if(array_key_exists($participant->getAid(),$row))
                        {
                            if($row[$participant->getAid()])
                            {
                                $actions+=$participant->getActions();
                                $votes+=$participant->getVotes();
                            }
                        }
                    }
                    $cActions=$candidates[$key-1]->getActionsCount();
                    $cVotes=$candidates[$key-1]->getVotesCount();
                    $cActions[$curr]=array('count'=>$actions,'percent'=>round(($actions/$meeting->getTotalActions())*100,2));
                    $cVotes[$curr]=array('count'=>$votes,'percent'=>round(($votes/$meeting->getTotalVotes())*100,2));
                    $candidates[$key-1]->setActionsCount($cActions);
                    $candidates[$key-1]->setVotesCount($cVotes);
                    if($meeting->getVariant()==1)
                    {
                        array_push($resultArray,array('candidate'=>$key-1,'result'=>$candidates[$key-1]->getVotesCount()[$active['turn']]['percent']));
                    }else{
                        array_push($resultArray,array('candidate'=>$key-1,'result'=>$candidates[$key-1]->getActionsCount()[$active['turn']]['percent']));
                    }
                    $em->persist($candidates[$key-1]);
                }
                $array_column = array_column($resultArray, "result");
                array_multisort( $array_column, SORT_DESC, $resultArray);
                if($resultArray[0]['result']>0)
                {
                    $candidates[$resultArray[0]['candidate']]->setSecondTurn(true);
                    $em->persist($candidates[$resultArray[0]['candidate']]);
                    if($resultArray[1]['result']>0)
                    {
                        $candidates[$resultArray[1]['candidate']]->setSecondTurn(true);
                        $em->persist($candidates[$resultArray[1]['candidate']]);
                        for($i=2;$i<sizeof($resultArray);$i++)
                        {
                            if($resultArray[1]['result']==$resultArray[$i]['result'])
                            {
                                $candidates[$resultArray[$i]['candidate']]->setSecondTurn(true);
                                $em->persist($candidates[$resultArray[$i]['candidate']]);
                            }else{
                                break;
                            }
                        }
                    }
                }

            }
        }

        $active['last']=$active['active'];
        $active['active']=null;
        $meeting->setActiveStatus($active);

        $em->persist($meeting);
        $em->flush();
        return new JsonResponse(array('status'=>'success'));
    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{id}/end", name="app_manage_general_meeting_end", methods={"PATCH"}))
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return JsonResponse
     */
    public function endGeneralMeeting(GeneralMeeting $meeting,Request $request)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return new JsonResponse(array('status'=>'error'));
        }
        if($meeting->getStatus()!==1)
        {
            return new JsonResponse(array('status'=>'error'));
        }

        $meeting->setStatus(2);
        $em=$this->getDoctrine()->getManager();
        $em->persist($meeting);
        $em->flush();
        return new JsonResponse(array('status'=>'success'));

    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{id}/reset", name="app_manage_general_meeting_reset", methods={"PATCH"}))
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return JsonResponse
     */
    public function resetGeneralMeeting(GeneralMeeting $meeting,Request $request)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return new JsonResponse(array('status'=>'error'));
        }
        if($meeting->getStatus()!==2)
        {
            return new JsonResponse(array('status'=>'error'));
        }
        $em=$this->getDoctrine()->getManager();
        $meeting->setStatus(1);
        if($meeting->getVariant()==1)
        {
            foreach ($meeting->getResolutions() as $resolution)
            {
                $resolution
                    ->setAccepted(null)
                    ->setVotesHoldCount(null)
                    ->setVotesOnCount(null)
                    ->setVotesHoldCount(null)
                    ->setVotesAgainstCount(null);
                $em->persist($resolution);
            }
            $meeting
                ->setTotalVotes(null)
                ->setTotalActions(null)
                ->setActiveStatus(array('active'=>null,'votes'=>array(),'last'=>null));

        }else{
            foreach ($meeting->getCandidates() as $candidate)
            {
                $candidate
                    ->setActionsCount(array())
                    ->setVotesCount(array())
                    ->setSecondTurn(false);
                $em->persist($candidate);
                $meeting->setActiveStatus(array('active'=>null,'votes'=>array(),'last'=>null,'title'=>null,'turn'=>null,'vote'=>array(),'voted'=>array()));
            }
        }

        $em->persist($meeting);
        $em->flush();
        return new JsonResponse(array('status'=>'success'));

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
        $aStatus=$meeting->getActiveStatus();
        $participants=$meeting->getParticipantList()->getParticipants();
        $actions=0; $votes=0;
        foreach ($participants as $participant)
        {
            $actions=$actions+$participant->getActions();
            $votes=$votes+$participant->getVotes();
        }


        return $this->render('polling/cockpit.html.twig',[
            'meeting'=>$meeting,
            'active'=>$aStatus,
            'hash'=>$meeting->getHashId(),
            'actions'=>$actions,
            'votes'=>$votes
        ]);
    }


    /**
     * @Route("/{_locale}/general_meeting/{slug}/join", name="app_general_meeting_join")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @param ParticipantRepository $repository
     * @return RedirectResponse|Response
     */
    public function generalMeetingJoin(GeneralMeeting $meeting,Request $request,ParticipantRepository $repository)
    {
        $session=$request->getSession();

        $uData=$session->get("user_gm_".$meeting->getSlug());
        if(!is_null($uData))
        {
            return $this->redirectToRoute('app_general_meeting_vote',['slug'=>$meeting->getSlug()]);
        }

        $form=$this->createForm(GeneralMeetingJoinType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()){
            $data=$form->getData();
            $participant=$repository->checkCredentials($meeting->getParticipantList(),$data);
            if(is_null($participant))
            {
                $form->get('password')->addError(new FormError($this->translator->trans("Wprowadzono nie aprawidłowe dane dostępowe")));
            }
            if($form->isValid())
            {
                $session->set("user_gm_".$meeting->getSlug(), array(
                        'name' => $participant->getName(),
                        'surname' => $participant->getSurname(),
                        'votes' => $participant->getVotes(),
                        'actions' => $participant->getActions(),
                        'aid' => $participant->getAid()
                    )
                );
                return $this->redirectToRoute('app_general_meeting_vote',['slug'=>$meeting->getSlug()]);
            }
        }

        return $this->render('polling/general_meeting_login.html.twig',[
            'form'=>$form->createView(),
            'meeting'=>$meeting
        ]);
    }


    /**
     * @Route("/{_locale}/general_meeting/{slug}/vote", name="app_general_meeting_vote")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function generalMeetingVote(GeneralMeeting $meeting,Request $request)
    {
        $session=$request->getSession();
        $participant=$session->get("user_gm_".$meeting->getSlug());
        if(is_null($participant))
        {
            return $this->redirectToRoute('app_general_meeting_join',['slug'=>$meeting->getSlug()]);
        }
        $aStatus=$meeting->getActiveStatus();
        $list=$meeting->getParticipantList()->getParticipants();

        return $this->render('polling/general_meeting_vote.html.twig',[
            'meeting'=>$meeting,
            'hash'=>$meeting->getHashId(),
            'active'=>$aStatus,
            'participant'=>$participant,
            'list'=>$list
        ]);
    }

    /**
     * @Route("/{_locale}/general_meeting/{slug}/activate_vote/{number}", name="app_general_meeting_activate_vote", methods={"PATCH"})
     * @param GeneralMeeting $meeting
     * @param int $number
     * @param Request $request
     * @return JsonResponse
     */
    public function activateVote(GeneralMeeting $meeting,int $number,Request $request)
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return new JsonResponse(array('status'=>'error'));
        }
        $em=$this->getDoctrine()->getManager();
        $active=$meeting->getActiveStatus();
        $active['active']=$number;
        $active['voted']=[];
        if($meeting->getVariant()==1)
        {
            $active['votes'][$number]=array();
        }
        if($meeting->getVariant()==2)
        {
            $votes_arr=[];
            $active['turn']=$number;
            foreach ($meeting->getCandidates() as $key=>$candidate)
            {
                if($active['turn']==1)
                {
                    $votes_arr[$key+1]=array();
                    $votes=$candidate->getVotesCount();
                    $actions=$candidate->getActionsCount();
                    $votes[1]=array();
                    $actions[1]=array();
                    $candidate->setVotesCount($votes)->setActionsCount($actions)->setSecondTurn(false);
                    $em->persist($candidate);
                }

                if($active['turn']==2)
                {
                    if($candidate->isSecondTurn())
                    {
                        $votes_arr[$key+1]=array();
                        $votes_arr[$key+1]=array();
                        $votes=$candidate->getVotesCount();
                        $actions=$candidate->getActionsCount();
                        $votes[2]=array();
                        $actions[2]=array();
                        $candidate->setVotesCount($votes)->setActionsCount($actions);
                        $em->persist($candidate);
                    }
                }
            }
            $active['votes'][$number]=$votes_arr;
        }
        $meeting->setActiveStatus($active);

        $em->persist($meeting);
        $em->flush();
        return new JsonResponse(array('status'=>'success'));

    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{id}/save_title", name="app_meeting_save_title", methods={"PATCH"})
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @return JsonResponse
     */
    public function saveMeetingTitle(GeneralMeeting $meeting,Request $request): JsonResponse
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return new JsonResponse(array('status'=>'error'));
        }
        $em=$this->getDoctrine()->getManager();
        $content=json_decode($request->getContent());
        $active=$meeting->getActiveStatus();
        $active['title']=$content->title;
        $meeting->setActiveStatus($active);
        $em->persist($meeting);
        $em->flush();
        return new JsonResponse(array('status'=>'success'));
    }
}
