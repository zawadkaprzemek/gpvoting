<?php

namespace App\Controller;

use App\Entity\Candidate;
use App\Entity\Event;
use App\Entity\GeneralMeeting;
use App\Entity\MeetingAnswer;
use App\Entity\MeetingVoting;
use App\Entity\Room;
use App\Form\GeneralMeetingJoinType;
use App\Form\GeneralMeetingType;
use App\Form\MeetingVotingType;
use App\Repository\MeetingVotingRepository;
use App\Repository\ParticipantRepository;
use App\Service\ExcelCourseGenerator;
use Doctrine\Common\Collections\ArrayCollection;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class GeneralMeetingController extends AbstractController
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
     * @Route("/{_locale}/manage/{slug_parent}/{slug_child}/create_general_meeting", name="app_manage_create_general_meeting")
     * @ParamConverter("event", options={"mapping": {"slug_parent": "slug"}})
     * @ParamConverter("room", options={"mapping": {"slug_child": "slug"}})
     * @param Event $event
     * @param Room $room
     * @param Request $request
     * @return Response
     */
    public function createGeneralMeeting(Event $event,Room $room,Request $request): Response
    {
        $meeting=new GeneralMeeting();
        $meeting->setRoom($room);
        $form=$this->createForm(GeneralMeetingType::class,$meeting);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();

            $meeting->setHashId(uniqid());
            $em->persist($meeting);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('general_meeting.new.success'));
            return $this->redirectToRoute('app_manage_general_meeting_add_voting',['slug'=>$meeting->getSlug()]);
        }

        return $this->render('general_meeting/general_meeting_form.html.twig',[
            'title'=>$this->translator->trans('general_meeting.new.title'),
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/{_locale}/manage/{slug}/add_voting", name="app_manage_general_meeting_add_voting")
     * @param Request $request
     * @param GeneralMeeting $meeting
     * @return RedirectResponse|Response
     */
    public function addMeetingVoting(Request $request,GeneralMeeting $meeting)
    {
        $voting=new MeetingVoting();
        for($i=0;$i<2;$i++)
        {
            $voting->addCandidate(new Candidate())->addAnswer(new MeetingAnswer());
        }
        $voting->setMeeting($meeting);
        $form=$this->createForm(MeetingVotingType::class,$voting);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $voting->setSort(sizeof($meeting->getMeetingVotings())+1);

            switch ($voting->getType())
            {
                case 1:
                    $voting->removeCandidates()->removeAnswers();
                    break;
                case 2:
                    $voting->removeAnswers();
                    foreach ($voting->getCandidates() as $candidate)
                    {
                        $em->persist($candidate);
                    }
                    break;
                case 3:
                    $voting->removeCandidates();
                    foreach ($voting->getAnswers() as $answer)
                    {
                        $answer->setMeetingVoting($voting);
                        $em->persist($answer);
                    }
                    break;
            }
            $em->persist($voting);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('general_meeting.voting.add.success'));
            if($form->get('add_next')->getData())
            {
                return $this->redirectToRoute('app_manage_general_meeting_add_voting',['slug'=>$meeting->getSlug()]);
            }else{
                return  $this->redirectToRoute('app_manage_show_vottings',['slug'=>$meeting->getSlug()]);
            }
        }
        return $this->render('general_meeting/general_meeting_form_voting.html.twig',[
            'title'=>$this->translator->trans('general_meeting.voting.add.title'),
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/{_locale}/manage/{slug}/edit/{sort}", name="app_manage_general_meeting_edit_voting")
     * @param Request $request
     * @param GeneralMeeting $meeting
     * @param MeetingVoting $voting
     * @return RedirectResponse|Response
     */
    public function editMeetingVoting(Request $request,GeneralMeeting $meeting,MeetingVoting $voting)
    {
        $form=$this->createForm(MeetingVotingType::class,$voting);

        $originalCandidates=new ArrayCollection();
        foreach ($voting->getCandidates() as $candidate)
        {
            $originalCandidates->add($candidate);
        }
        $originalAnswers=new ArrayCollection();
        foreach ($voting->getAnswers() as $answer)
        {
            $originalAnswers->add($answer);
        }
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            switch ($voting->getType())
            {
                case 1:
                    $voting->removeCandidates()->removeAnswers();
                    break;
                case 2:
                    $voting->removeAnswers();
                    foreach ($originalCandidates as $candidate)
                    {
                        if(false === $voting->getCandidates()->contains($candidate))
                        {
                            $em->remove($candidate);
                        }
                    }
                    foreach ($voting->getCandidates() as $candidate)
                    {
                        $em->persist($candidate);
                    }
                    break;
                case 3:
                    $voting->removeCandidates();
                    foreach ($originalAnswers as $answer)
                    {
                        if(false === $voting->getAnswers()->contains($answer))
                        {
                            $em->remove($answer);
                        }
                    }
                    foreach ($voting->getAnswers() as $answer)
                    {
                        $answer->setMeetingVoting($voting);
                        $em->persist($answer);
                    }
                    break;
            }
            $em->persist($voting);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('general_meeting.voting.edit.success'));
            if($voting->getMeeting()->getStatus()==1)
            {
                return $this->redirectToRoute('app_manage_general_meeting_cockpit',['slug' =>$voting->getMeeting()->getSlug()]);
            }else{
                return  $this->redirectToRoute('app_manage_show_vottings',['slug'=>$meeting->getSlug()]);
            }
        }
        return $this->render('general_meeting/general_meeting_form_voting.html.twig',[
            'title'=>$this->translator->trans('general_meeting.voting.edit.title'),
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/{_locale}/manage/voting/{id}/delete", name="app_manage_meeting_voting_delete", methods={"DELETE"})
     * @param Request $request
     * @param MeetingVoting $voting
     * @return Response
     */
    public function delete(Request $request, MeetingVoting $voting): Response
    {
        $user=$this->getUser();
        if($voting->getMeeting()->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        if ($this->isCsrfTokenValid('delete'.$voting->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($voting);
            $meeting=$voting->getMeeting();
            foreach ($voting->getCandidates() as $candidate)
            {
                $entityManager->remove($candidate);
            }
            foreach ($voting->getAnswers() as $answer)
            {
                $entityManager->remove($answer);
            }
            foreach ($meeting->getMeetingVotings() as $mVoting)
            {
                if($mVoting->getSort()>$voting->getSort())
                {
                    $mVoting->setSort($mVoting->getSort()-1);
                    $entityManager->persist($mVoting);
                }
            }
            $entityManager->flush();
            $this->addFlash('success','Usunięto głosowanie');
        }
        return $this->redirectToRoute('app_manage_show_vottings',[
            'slug'=>$voting->getMeeting()->getSlug()
        ]);
    }

    /**
     * @Route("/{_locale}/manage/meeting/{slug}/vottings", name="app_manage_show_vottings")
     * @param GeneralMeeting $meeting
     * @return RedirectResponse|Response
     */
    public function showVottings(GeneralMeeting $meeting)
    {
        if($this->getUser()!=$meeting->getRoom()->getEvent()->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }

        $votings=$meeting->getMeetingVotings();

        return $this->render('general_meeting/vottings_list.html.twig',[
            'meeting'=>$meeting,
            'votings'=>$votings
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

        $votings=$meeting->getMeetingVotings();
        return $this->render('polling/general_meeting_show.html.twig',[
            'meeting'=>$meeting,
            'manage'=>true,
            'votings'=>$votings
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

        if($meeting->getStatus()==1)
        {
            $this->addFlash('warning',$this->translator->trans("general_meeting.edit.cannot_edit"));
            if(is_null($request->server->get('HTTP_REFERER')))
            {
                return $this->redirectToRoute('app_manage_general_meeting_cockpit',['slug'=>$meeting->getSlug()]);
            }else{
                return $this->redirect($request->server->get('HTTP_REFERER'));
            }

        }

        $form=$this->createForm(GeneralMeetingType::class,$meeting);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($meeting);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('general_meeting.edit.success'));
            return $this->redirectToRoute('app_manage_general_meeting_cockpit',['slug'=>$meeting->getSlug()]);
        }

        return $this->render('general_meeting/general_meeting_form.html.twig',[
            'form'=>$form->createView(),
            'title'=>$this->translator->trans('general_meeting.edit.title'),
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
        $active=array('active'=>null,'votes'=>array(),'last'=>null,'kworum'=>null,'kworum_value'=>0);
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
        $meeting->setActiveVoting(0);
        $active["votes"]=array();
        if($meeting->getKworum())
        {
            $active['kworum']=null;
            $meeting->setKworumValue(0);
        }
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
     * @param MeetingVotingRepository $repository
     * @return JsonResponse
     */
    public function generalMeetingVoteSave(GeneralMeeting $meeting,Request $request,MeetingVotingRepository $repository): JsonResponse
    {
        $data=json_decode($request->getContent());
        $active=$meeting->getActiveStatus();
        $em=$this->getDoctrine()->getManager();
        if($meeting->getActiveVoting()==0)
        {
            $active['votes'][$data->user]=$data->vote;
            $meeting->setActiveStatus($active);
        }else{
            /**
             * @var $voting MeetingVoting
             */
            $voting=$repository->getVotingBySort($meeting,$meeting->getActiveVoting());
            $votes=$voting->getVoteStatus();

            switch ($voting->getType())
            {
                case 1:
                    $votes[$data->user]=$data->vote;
                    break;
                case 2:
                    foreach ($data->votes as $vote)
                    {
                        $votes[$vote][$data->user]=$data->valid;
                    }
                    break;
                case 3:
                    foreach ($data->votes as $vote)
                    {
                        $votes[$vote][]=$data->user;
                        $votes[$vote]=array_unique($votes[$vote]);
                    }
                    break;
                default:
                    break;
            }
            $active['voted'][]=$data->user;
            $active['voted']=array_unique($active['voted']);

            $voting->setVoteStatus($votes);
            $meeting->setActiveStatus($active);
            $em->persist($voting);
        }

        $em->persist($meeting);
        $em->flush();
        return new JsonResponse(array('status'=>'success'));
    }

    /**
     * @Route("/{_locale}/general_meeting/{slug}/end_vote", name="app_ajax_general_meeting_end_vote", methods={"PATCH"})
     * @param GeneralMeeting $meeting
     * @return JsonResponse
     */
    public function generalMeetingEndVote(GeneralMeeting $meeting): JsonResponse
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return new JsonResponse(array('status'=>'error'));
        }
        $em=$this->getDoctrine()->getManager();
        $active=$meeting->getActiveStatus();
        $participants=$meeting->getParticipantList()->getAcceptedParticipants();
        if($meeting->getActiveVoting()==0)
        {
            $tot_a=0;
            $tot_v=0;
            $abs_a=0;
            $abs_v=0;
            foreach ($participants as $participant)
            {
                if(array_key_exists($participant->getAid(),$active['votes']))
                {
                    $tot_a=$tot_a+$participant->getActions();
                    $tot_v=$tot_v+$participant->getVotes();
                }else{
                    $abs_a=$abs_a+$participant->getActions();
                    $abs_v=$abs_v+$participant->getVotes();
                }

            }
            if($meeting->getKworum())
            {
                switch($meeting->getKworumType())
                {
                    case '1to1':
                        $percent=round((sizeof($active['votes'])/sizeof($participants))*100,2);
                        break;
                    case 'actions':
                        $total=0;
                        $actions=0;
                        foreach ($participants as $participant)
                        {
                            if(array_key_exists($participant->getAid(),$active['votes']))
                            {
                                $actions+=$participant->getActions();
                            }
                            $total+=$participant->getActions();
                        }
                        $percent=round(($actions/$total)*100,2);
                        break;

                    case 'votes':
                        $total=0;
                        $votes=0;
                        foreach ($participants as $participant)
                        {
                            if(array_key_exists($participant->getAid(),$active['votes']))
                            {
                                $votes+=$participant->getVotes();
                            }
                            $total+=$participant->getVotes();
                        }
                        $percent=round(($votes/$total)*100,2);
                        break;
                }


                $active['kworum']=$percent>=$meeting->getKworumRequiredValue();
                //$active['kworum_value']=$percent;
                $meeting->setKworumValue($percent);
            }
            $meeting
                ->setTotalActions($tot_a)->setTotalVotes($tot_v)
                ->setAbsenceActions($abs_a)->setAbsenceVotes($abs_v)
                ->setActiveStatus($active)
                ->setActiveVoting(null)
            ;
        }else{
            $repository=$em->getRepository(MeetingVoting::class);
            /** @var MeetingVoting $voting */
            $voting=$repository->getVotingBySort($meeting,$meeting->getActiveVoting());
            switch ($voting->getType())
            {
                case 1:
                    $results=array('votes'=>array(1=>0,0=>0,2=>0),'actions'=>array(1=>0,0=>0,2=>0));
                    foreach ($voting->getVoteStatus() as $key=>$vote)
                    {
                        foreach ($participants as $participant)
                        {
                            if($participant->getAid()===$key)
                            {
                                $results['votes'][$vote]+=$participant->getVotes();
                                $results['actions'][$vote]+=$participant->getActions();
                                continue;
                            }
                        }
                    }
                    foreach ($results['votes'] as $vote=>$val)
                    {
                        if($meeting->getTotalVotes()>0)
                        {
                            $results['votes'][$vote]=round(($val/$meeting->getTotalVotes())*100,2);
                        }else{
                            $results['votes'][$vote]=0;
                        }

                    }
                    foreach ($results['actions'] as $vote=>$val)
                    {
                        if($meeting->getTotalActions()>0) {
                            $results['actions'][$vote] = round(($val / $meeting->getTotalActions()) * 100, 2);
                        }else{
                            $results['actions'][$vote] =0;
                        }
                    }
                    $results['votes']['accepted']=($results['votes'][1]>$results['votes'][0]&&$results['votes'][1]>$results['votes'][2]);
                    $results['actions']['accepted']=($results['actions'][1]>$results['actions'][0]&&$results['actions'][1]>$results['actions'][2]);
                    $voting->setVotesSummary($results);
                    break;
                case 2:
                    $results=array('valid'=>array(),'invalid'=>array());
                    for($i=0;$i<sizeof($voting->getCandidates());$i++)
                    {
                        $results['valid']['votes'][$i]=0;
                        $results['valid']['actions'][$i]=0;
                    }
                    $results['invalid']=[];
                    foreach ($voting->getVoteStatus() as $vote =>$status)
                    {
                        foreach ($status as $aid => $valid)
                        {
                            if($valid)
                            {
                                foreach ($participants as $participant)
                                {
                                    if($aid===$participant->getAid())
                                    {
                                        $results['valid']['votes'][$vote-1]+=$participant->getVotes();
                                        $results['valid']['actions'][$vote-1]+=$participant->getActions();
                                    }
                                }
                            }else{
                                foreach ($participants as $participant)
                                {
                                    if($aid===$participant->getAid())
                                    {
                                        $results['invalid'][]=$aid;
                                    }
                                }
                            }

                        }
                    }
                    $results['invalid']=count(array_unique($results['invalid']));

                    foreach ($results['valid']['votes'] as $vote=>$val)
                    {
                        if($meeting->getTotalVotes()>0)
                        {
                            $results['valid']['votes'][$vote]=round(($val/$meeting->getTotalVotes())*100,2);
                        }else{
                            $results['valid']['votes'][$vote]=0;
                        }

                    }

                    foreach ($results['valid']['actions'] as $vote=>$val)
                    {
                        if($meeting->getTotalActions()>0)
                        {
                            $results['valid']['actions'][$vote]=round(($val/$meeting->getTotalActions())*100,2);
                        }else{
                            $results['valid']['actions'][$vote]=0;
                        }

                    }

                    arsort($results['valid']['votes']);
                    arsort($results['valid']['actions']);
                    $voting->setVotesSummary($results);
                    break;
                case 3:
                    $results=array();
                    for($i=0;$i<sizeof($voting->getAnswers());$i++)
                    {
                        $results[$i]=0;
                    }
                    foreach ($voting->getVoteStatus() as $vote =>$votes)
                    {
                        foreach ($participants as $participant)
                        {
                            if(in_array($participant->getAid(),$votes))
                            {
                                $results[$vote-1]++;
                            }
                        }

                    }
                    foreach ($results as $vote=>$val)
                    {
                        if(sizeof($active['voted'])>0)
                        {
                            $results[$vote]=round(($val/sizeof($active['voted']))*100,2);
                        }else{
                            $results[$vote]=0;
                        }

                    }
                    arsort($results);
                    $voting->setVotesSummary($results);
                    break;
                default:
                    break;
            }
            $voting->setStatus(2);
            $em->persist($voting);
        }

        $meeting->setLastVoting($meeting->getActiveVoting());
        $meeting->setActiveVoting(null);
        /*$active['last']=$active['active'];
        $active['active']=null;*/
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
    public function endGeneralMeeting(GeneralMeeting $meeting,Request $request): JsonResponse
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
     * @return JsonResponse
     */
    public function resetGeneralMeeting(GeneralMeeting $meeting): JsonResponse
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
        $active=array('votes'=>array(),'kworum'=>null);
        $meeting
            ->setActiveStatus($active)
            ->setActiveVoting(null)
            ->setLastVoting(null)
            ->setKworumValue(null)
            ;
        foreach ($meeting->getMeetingVotings() as $voting)
        {
            $voting->setStatus(0)->setVotesSummary(array())->setVoteStatus(array());
            $em->persist($voting);
        }

        $em->persist($meeting);
        $em->flush();
        return new JsonResponse(array('status'=>'success'));

    }


    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/cockpit", name="app_manage_general_meeting_cockpit")
     * @param GeneralMeeting $meeting
     * @param MeetingVotingRepository $repository
     * @return RedirectResponse|Response
     */
    public function generalMeetingCockpit(GeneralMeeting $meeting,MeetingVotingRepository $repository):Response
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        $aStatus=$meeting->getActiveStatusArray();
        if(!is_null($meeting->getParticipantList()))
        {
            $participants=$meeting->getParticipantList()->getAcceptedParticipants();
        }else{
            $participants=array();
        }
        $actions=0; $votes=0;
        foreach ($participants as $participant)
        {
            $actions=$actions+$participant->getActions();
            $votes=$votes+$participant->getVotes();
        }
        if(isset($aStatus['active']) and !is_null($aStatus['active']))
        {
            $voting=$repository->getVotingBySort($meeting,$aStatus['active']);
        }else{
            $voting=null;
        }

        return $this->render('general_meeting/cockpit.html.twig',[
            'meeting'=>$meeting,
            'active'=>$aStatus,
            'hash'=>$meeting->getHashId(),
            'actions'=>$actions,
            'votes'=>$votes,
            'voting'=>$voting,
            'participants'=>$participants
        ]);
    }


    /**
     * @Route("/{_locale}/general_meeting/{slug}/join", name="app_general_meeting_join")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @param ParticipantRepository $repository
     * @return RedirectResponse|Response
     */
    public function generalMeetingJoin(GeneralMeeting $meeting,Request $request,ParticipantRepository $repository):Response
    {
        if(!$meeting->getRoom()->getVisible())
        {
            return $this->redirectToRoute('home');
        }
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
                $form->get('password')->addError(new FormError($this->translator->trans("form.error.wrong_credentials")));
            }

            if($form->isValid())
            {
                $session->set("user_gm_".$meeting->getSlug(), array(
                        'id'=>$participant->getId(),
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
     * @param MeetingVotingRepository $repository
     * @param ParticipantRepository $participantRepository
     * @return RedirectResponse|Response
     */
    public function generalMeetingVote(GeneralMeeting $meeting,Request $request,MeetingVotingRepository $repository,ParticipantRepository $participantRepository):Response
    {
        $session=$request->getSession();
        $participant=$session->get("user_gm_".$meeting->getSlug());
        //$session->remove("user_gm_".$meeting->getSlug());
        if(is_null($participant))
        {
            return $this->redirectToRoute('app_general_meeting_join',['slug'=>$meeting->getSlug()]);
        }
        $participant=$participantRepository->find($participant['id']);
        if(!$participant->getAccepted())
        {
            return $this->render('general_meeting/permission_denied.html.twig', array(
               'participant' => $participant,
                'meeting'=>$meeting
            ));
        }
        $aStatus=$meeting->getActiveStatusArray();
        if(isset($aStatus['active']) && !is_null($aStatus['active']))
        {
            $voting=$repository->getVotingBySort($meeting,$aStatus['active']);
        }else{
            $voting=null;
        }
        if($aStatus['last']>0)
        {
            $last=$repository->getVotingBySort($meeting,$aStatus['last']);
        }else{
            $last=null;
        }

        return $this->render('polling/general_meeting_vote.html.twig',[
            'meeting'=>$meeting,
            'hash'=>$meeting->getHashId(),
            'active'=>$aStatus,
            'participant'=>$participant,
            'voting'=>$voting,
            'last'=>$last
        ]);
    }

    /**
     * @Route("/{_locale}/general_meeting/{slug}/activate_vote/{sort}", name="app_general_meeting_activate_vote", methods={"PATCH"})
     * @param GeneralMeeting $meeting
     * @param MeetingVoting $voting
     * @return JsonResponse
     */
    public function activateVote(GeneralMeeting $meeting,MeetingVoting $voting): JsonResponse
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return new JsonResponse(array('status'=>'error'));
        }
        $em=$this->getDoctrine()->getManager();
        $active=$meeting->getActiveStatus();
        $meeting->setActiveVoting($voting->getSort());
        $active['voted']=array();
        $summary=$voting->getVotesSummary();
        $history=$voting->getHistoricalResults();
        if(!empty($summary))
        {
            array_unshift($history,$summary);
            $voting->setHistoricalResults($history);
        }
        $voting->setStatus(1)->setVoteStatus(array())->setVotesSummary(array());

        $meeting->setActiveStatus($active);

        $em->persist($meeting);
        $em->persist($voting);
        $em->flush();
        return new JsonResponse(array('status'=>'success'));
    }

    /**
     * @Route("{_locale}/manage/general_meeting/{id}/duplicate", name="app_manage_general_meeting_duplicate", methods={"PATCH"})
     * @param GeneralMeeting $meeting
     * @return RedirectResponse
     */
    public function generalMeetingDuplicate(GeneralMeeting $meeting): RedirectResponse
    {
        if($meeting->getRoom()->getEvent()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }

        $em=$this->getDoctrine()->getManager();
        $duplicate=clone $meeting;
        $votings=$meeting->getMeetingVotings();
        foreach ($votings as $voting)
        {
            $d_voting=clone $voting;
            foreach ($voting->getCandidates() as $candidate)
            {
                $d_candidate=clone $candidate;
                $d_candidate->setMeetingVoting($d_voting);
                $em->persist($d_candidate);
            }
            foreach ($voting->getAnswers() as $answer)
            {
                $d_answer=clone $answer;
                $d_answer->setMeetingVoting($d_voting);
                $em->persist($d_answer);
            }
            $duplicate->removeMeetingVoting($voting);
            $duplicate->addMeetingVoting($d_voting);
            $em->persist($d_voting);
        }
        $em->persist($duplicate);
        $em->flush();
        $this->addFlash('success',$this->translator->trans('duplicate_success'));
        return $this->redirectToRoute('app_manage_room',[
            'slug_child'=>$meeting->getRoom()->getSlug(),
            'slug_parent'=>$meeting->getRoom()->getEvent()->getSlug()
        ]);
    }


    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/course/{sort}", name="app_manage_general_meeting_course_voting")
     * @param GeneralMeeting $meeting
     * @param MeetingVoting $voting
     * @return Response
     */
    public function courseVoting(GeneralMeeting $meeting,MeetingVoting $voting): Response
    {
        return $this->render('general_meeting/course_voting.html.twig',[
           'meeting'=>$meeting,
           'voting'=>$voting
        ]);
    }

    /**
     * @Route("/{_locale}/manage/general_meeting/{slug}/download_course", name="app_manage_general_meeting_course_meeting")
     * @param GeneralMeeting $meeting
     * @param Request $request
     * @param ExcelCourseGenerator $generator
     * @return RedirectResponse|StreamedResponse
     */
    public function courseMeeting(GeneralMeeting $meeting,Request $request,ExcelCourseGenerator $generator)
    {
        if($meeting->getSecret())
        {
            return $this->redirect($request->server->get('HTTP_REFERER'));
        }

        $filename=str_replace(' ','_',strtolower($meeting->getName())).'_przebieg_glosowania.xlsx';
        $streamedResponse = new StreamedResponse();
        $streamedResponse->setCallback(function () use ($generator, $meeting) {
            $excel=$generator->createExcel($meeting);
            $writer =  new Xlsx($excel);
            $writer->save('php://output');
        });
        $streamedResponse->setStatusCode(Response::HTTP_OK);
        $streamedResponse->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $streamedResponse->headers->set('Content-Disposition', 'attachment; filename="'.$filename.'"');

        return $streamedResponse->send();
    }


}
