<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\ParticipantList;
use App\Entity\User;
use App\Form\ImportParticipantsType;
use App\Form\ParticipantListType;
use App\Form\ParticipantType;
use App\Message\EmailMessage;
use App\Repository\ParticipantListRepository;
use App\Repository\ParticipantRepository;
use SimpleXLSX;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Message;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParticipantController extends AbstractController
{
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;
    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;
    private MessageBusInterface $messageBus;

    public function __construct(TranslatorInterface $translator,MailerInterface $mailer,MessageBusInterface $messageBus)
    {
        $this->translator = $translator;
        $this->mailer = $mailer;
        $this->messageBus = $messageBus;
    }

    /**
     * @Route("/manage/participants_lists/show", name="app_manage_participants_lists")
     * @param ParticipantListRepository $repository
     * @return Response
     */
    public function listAction(ParticipantListRepository $repository): Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        /** @var User $user */
        $user=$this->getUser();

        $lists=$repository->getUsersLists($user);

        return $this->render('participant/lists.html.twig',[
            'lists'=>$lists
        ]);
    }

    /**
     * @Route("/manage/participant_lists/new", name="app_manage_participant_list_new")
     * @param Request $request
     * @return Response
     */
    public function newListAction(Request $request): Response
    {
        $list= new ParticipantList();
        /** @var User $user */
        $user=$this->getUser();
        $list->setUser($user);
        $form=$this->createForm(ParticipantListType::class,$list);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $list->setHashId(md5($list->getName()));
            $em->persist($list);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('participants.list.new.success'));
            return $this->redirectToRoute('app_manage_participants_lists');
        }

        return $this->render('participant/lists_form.html.twig',[
           'form'=>$form->createView(),
            'title'=>$this->translator->trans('participants.list.new.text')
        ]);
    }

    /**
     * @Route("/manage/participants_lists/{id}/edit", name="app_manage_participant_list_edit")
     * @param ParticipantList $list
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function edit(ParticipantList $list,Request $request):Response
    {
        if($list->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        $form=$this->createForm(ParticipantListType::class,$list);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($list);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('participants.list.edit.success'));
            return $this->redirectToRoute('app_manage_participants_lists');
        }

        return $this->render('participant/lists_form.html.twig',[
            'form'=>$form->createView(),
            'title'=>$this->translator->trans('participants.list.edit.text')
        ]);
    }

    /**
     * @Route("/manage/participants_lists/{id}/show", name="app_manage_participant_list_show")
     * @param ParticipantList $list
     * @return RedirectResponse|Response
     */
    public function showListAction(ParticipantList $list):Response
    {
        if($list->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }

        return $this->render('participant/show_list.html.twig',[
            'list'=>$list
        ]);
    }

    /**
     * @Route("/manage/participants_lists/{id}/participants", name="app_manage_participant_list_show_participants")
     * @param ParticipantList $list
     * @param ParticipantRepository $repository
     * @return RedirectResponse|Response
     */
    public function showParticipantsAction(ParticipantList $list,ParticipantRepository $repository):Response
    {
        if($list->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }

        $participants=$repository->getParticipantsFromList($list);

        return $this->render('participant/participants.html.twig',[
            'participants'=>$participants,
            'list'=>$list
        ]);
    }


    /**
     * @Route("/manage/participants_lists/{id}/import", name="app_manage_participant_list_import")
     * @param ParticipantList $list
     * @param Request $request
     * @param ParticipantRepository $repository
     * @return RedirectResponse|Response
     */
    public function importParticipants(ParticipantList $list,Request $request,ParticipantRepository $repository):Response
    {
        if($list->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        $form=$this->createForm(ImportParticipantsType::class);
        $form->handleRequest($request);
        $stats=array('new'=>array(),'exists'=>array());
        if($form->isSubmitted()&&$form->isValid())
        {
            $number=$list->getCount();
            $em=$this->getDoctrine()->getManager();
            $file=$form->getData()['file'];
            try{
                $xlsx=SimpleXLSX::parse($file);
                if((sizeof($list->getParticipants())+sizeof($xlsx->rows()))>$list->getUser()->getParticipantListSize())
                {
                    $this->addFlash('danger',$this->translator->trans("import_error.too_many_participants",
                        ['%max%'=>$list->getUser()->getParticipantListSize()-sizeof($list->getParticipants())]));

                    return $this->redirectToRoute("app_manage_participant_list_import",['id'=>$list->getId()]);
                }
                foreach ($xlsx->rows() as $row)
                {
                    $participant=$repository->getParticipantByEmail($list,$row[3]);
                    if(is_null($participant))
                    {
                        $participant=new Participant();
                        $participant
                            ->setName($row[0])
                            ->setPhone($row[1])
                            ->setEmail(trim($row[2]))
                            ->setVotes($row[3])
                            ->setActions($row[4])
                            ->setList($list)
                            ->setAid("A".++$number)
                            ->setAccepted(true)
                            ->setVerified(false)
                            ->setHash(md5($participant->getName().$participant->getEmail()))
                        ;
                        $participant->setPlainPass($this->generateRandomString(12))->setPassword(md5($participant->getPlainPass()));
                        $em->persist($participant);
                        $stats['new'][]=$participant;
                    }else{
                        $stats['exists'][]=$participant;
                    }
                }
                $list->setCount($number);
                $em->persist($list);
                $em->flush();
                foreach ($stats['new'] as $participant)
                {
                    $this->messageBus->dispatch(
                        new EmailMessage($participant,'participant_credentials_mail')
                    );
                    //$this->sendEmailWithPassword($participant->getEmail(),$participant->getPlainPass());
                }
                $this->addFlash('success',$this->translator->trans('participants_import_success',
                    ['%new%'=>sizeof($stats['new']),'%exists%'=>sizeof($stats['exists'])]));
                return $this->redirectToRoute('app_manage_participant_list_show',['id'=>$list->getId()]);
            }catch(\Throwable $ex)
            {
                $error = $ex->getMessage();
            }
        }

        return $this->render('participant/import.html.twig',[
            'form'=>$form->createView(),
            'list'=>$list,
            'error'=>($error ?? null)
        ]);
    }

    /**
     * @Route("/assign_to_list/{hashId}/", name="app_participant_list_assign")
     * @param ParticipantList $list
     * @param Request $request
     * @return Response
     */
    public function signToListAction(ParticipantList $list,Request $request): Response
    {
        $em=$this->getDoctrine()->getManager();
        if(sizeof($list->getParticipants())>=$list->getUser()->getParticipantListSize())
        {
            $list->setOpen(false);
            $em->persist($list);
            $em->flush();
            return $this->redirectToRoute("app_participant_list_assign",['hashId'=>$list->getHashId()]);
        }

        $participant=new Participant();
        $participant->setList($list);
        $form=$this->createForm(ParticipantType::class,$participant);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $number=$list->getCount();

            $participant->setPlainPass($participant->getPassword())
                ->setPassword(md5($participant->getPlainPass()))
                ->setAid("A".++$number)
                ->setVerified(true)
                ->setHash(md5($participant->getName().$participant->getEmail()))
            ;

            $em->persist($participant);
            $list->setCount($number);
            $em->persist($list);
            $em->flush();
            $session=$request->getSession();
            $session->set("assign_".$list->getHashId(),$participant->getAid());
            return $this->redirectToRoute('app_participant_list_assign_complete',['hashId'=>$list->getHashId()]);
        }

        return $this->render('participant/assign_form.html.twig',[
           'form'=>$form->createView(),
           'list'=>$list,
            'edit'=>false
        ]);
    }

    /**
     * @Route("/assign_to_list/{hashId}/complete", name="app_participant_list_assign_complete")
     * @param ParticipantList $list
     * @param Request $request
     * @return Response
     */
    public function assignCompleteAction(ParticipantList $list,Request $request): Response
    {
        $session=$request->getSession();
        $aid=$session->get("assign_".$list->getHashId());
        if(is_null($aid))
        {
            return $this->redirectToRoute('app_participant_list_assign',['hashId'=>$list->getHashId()]);
        }
        return $this->render('participant/assign_complete.html.twig',[
            'list'=>$list,
            'aid'=>$aid
        ]);
    }

    /**
     * @Route("/manage/participant/{id}/edit", name="app_manage_participant_edit")
     * @param Participant $participant
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editParticipantAction(Participant $participant,Request $request):Response
    {
        if($participant->getList()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }

        $form=$this->createForm(ParticipantType::class,$participant);
        $form->remove('password');
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($participant);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('participants.single.edit.success'));
            return $this->redirectToRoute('app_manage_participant_list_show_participants',['id'=>$participant->getList()->getId()]);
        }

        return $this->render('participant/assign_form.html.twig',[
           'form'=>$form->createView(),
            'list'=>$participant->getList(),
            'edit'=>true
        ]);
    }

    /**
     * @Route("/ajax/participant_list/{id}/open", name="app_participant_list_open")
     * @param ParticipantList $list
     * @return RedirectResponse
     */
    public function openCloseAction(ParticipantList $list):RedirectResponse
    {
        if($list->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        $list->setOpen(!$list->getOpen());
        if($list->getOpen())
        {
            if(sizeof($list->getParticipants())>=$list->getUser()->getParticipantListSize())
            {
                $this->addFlash('danger',$this->translator->trans("list_full_cannot_open"));
                return $this->redirectToRoute('app_manage_participant_list_show',['id'=>$list->getId()]);
            }

        }

        $em=$this->getDoctrine()->getManager();
        $em->persist($list);
        $em->flush();
        if($list->getOpen())
        {
            $this->addFlash('success',$this->translator->trans('participants.list.open.success'));

        }else{
            $this->addFlash('success',$this->translator->trans('participants.list.close.success'));
        }
        return $this->redirectToRoute('app_manage_participant_list_show',['id'=>$list->getId()]);
    }

    /**
     * @Route("/manage/participant/{id}/delete", name="app_manage_participant_delete", methods={"DELETE"})
     * @param Participant $participant
     * @return RedirectResponse
     */
    public function removeParticipant(Participant $participant)
    {
        if($participant->getList()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }

        $em=$this->getDoctrine()->getManager();
        $em->remove($participant);
        $em->flush();
        $this->addFlash('success',$this->translator->trans("participants.single.delete.success"));
        return $this->redirectToRoute("app_manage_participant_list_show_participants",['id'=>$participant->getList()->getId()]);
    }

    /**
     * @Route("/manage/participant/{id}/accept", name="app_manage_participant_accept", methods={"PATCH"})
     * @param Participant $participant
     * @return RedirectResponse
     */
    public function acceptParticipantAction(Participant $participant): RedirectResponse
    {
        if($participant->getList()->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }

        $participant->setAccepted(!$participant->getAccepted());
        $em=$this->getDoctrine()->getManager();
        $em->persist($participant);
        $em->flush();
        $this->addFlash('success',($participant->getAccepted()? $this->translator->trans("participants.single.accept.success"): $this->translator->trans("participants.single.reject.success")));
        return $this->redirectToRoute("app_manage_participant_list_show_participants",['id'=>$participant->getList()->getId()]);
    }

    /**
     * @Route("/manage/participant_list/{id}/accept", name="app_manage_participant_list_accept", methods={"PATCH"})
     * @param Request $request
     * @param ParticipantList $list
     * @return RedirectResponse
     */
    public function acceptParticipantListAction(Request $request, ParticipantList $list): RedirectResponse
    {
        if($list->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }
        $em=$this->getDoctrine()->getManager();
        $newStatus=!$list->isAccepted();
        foreach($list->getParticipants() as $participant)
        {
            $participant->setAccepted($newStatus);
            $em->persist($participant);
        }
        $em->flush();
        $this->addFlash('success',($newStatus ? $this->translator->trans('participants.list.accept.success') : $this->translator->trans('participants.list.reject.success')));
        return $this->redirect($request->headers->get('referer'));
    }

    private function generateRandomString($length = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ_-!';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    private function sendEmailWithPassword($email, $password)
    {
        $mail = (new Email())
            ->from('kontakt@gpvoting.pl')
            ->priority(Email::PRIORITY_HIGH)
            ->subject('Hasło dostępu uczestnika')
            ->addTo($email)
            ->html($this->renderView(
                'email/participant_password_email.html.twig',['password'=>$password]
            ));
        $this->mailer->send($mail);
    }

    /**
     * @Route("/verify_participant/{hash}/accept", name="app_participant_verify")
     * @param string $hash
     * @return Response
     */
    public function verifyParticipant(string $hash): Response
    {
        $em=$this->getDoctrine()->getManager();
        $participant = $em->getRepository(Participant::class)->getParticipantByHash($hash);

        if($participant){
            $participant->setVerified(true);
            $em->persist($participant);
            $em->flush();
            $message = 'participants.verified.success';
        }else{
            $message = 'participants.verified.error';
        }

        return $this->render('participant/verified.html.twig',[
            'message' => $message
        ]);
    }
}
