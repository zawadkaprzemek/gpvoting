<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\EventCode;
use App\Entity\User;
use App\Form\AddCodesType;
use App\Form\EventType;
use App\Repository\RoomRepository;
use Gedmo\Sluggable\Util\Urlizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class EventController extends AbstractController
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
     * @Route("/{_locale}/manage/create_event", name="app_event_create")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function newEvent(Request $request):Response
    {
        $this->denyAccessUnlessGranted("ROLE_USER");
        $event=new Event();
        /** @var User $user */
        $user=$this->getUser();
        $pack=$user->getPack();
        if($pack===null || sizeof($user->getEvents())>=$pack->getEventsCount())
        {
            $this->addFlash('danger',$this->translator->trans('event.error.limit_used_up'));
            return $this->redirectToRoute('home');
        }
        $event->setUser($user)->setOrganizer($user->getUsername());
        $form=$this->createForm(EventType::class,$event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['logoFile']->getData();
            $destination = $this->getParameter('upload_image_directory');
            $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
            $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
            $em=$this->getDoctrine()->getManager();
            $event->setLogo($newFilename);
            $codes=$this->generateEventCodes($event);
            foreach ($codes as $code)
            {
                $event->addEventCode($code);
                $em->persist($code);
            }
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', $this->translator->trans('event.add.success'));
            return $this->redirectToRoute('app_manage_event_show',['slug'=>$event->getSlug()]);
        }
        return $this->render('event/form.html.twig',[
           'form'=>$form->createView(),
            'title'=>$this->translator->trans("event.add.new")
        ]);
    }

    /**
     * @Route("/{_locale}/manage/{slug}/edit", name="app_event_edit")
     * @param Event $event
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editEvent(Event $event,Request $request):Response
    {
        $user=$this->getUser();
        if($event->getUser()!==$user)
        {
            return $this->redirectToRoute('app_event_list');
        }
        $oldLogoName=$event->getLogo();
        $form=$this->createForm(EventType::class,$event);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $changeLogo=$form->get('changeLogo')->getData();
            if($changeLogo)
            {
                /** @var UploadedFile $uploadedFile */
                $uploadedFile = $form['logoFile']->getData();
                $destination = $this->getParameter('upload_image_directory');
                $originalFilename = pathinfo($uploadedFile->getClientOriginalName(), PATHINFO_FILENAME);
                $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
                $uploadedFile->move(
                    $destination,
                    $newFilename
                );
                $filesystem = new Filesystem();
                $oldLogo=$this->getParameter('upload_image_directory').$oldLogoName;
                if($filesystem->exists($oldLogo))
                {
                    $filesystem->remove($oldLogo);
                }
                $event->setLogo($newFilename);
            }else{
                $event->setLogo($oldLogoName);
            }
            $em=$this->getDoctrine()->getManager();
            $em->persist($event);
            $em->flush();
            $this->addFlash('success', $this->translator->trans('changes_saved'));
            return $this->redirectToRoute('app_manage_event_show',['slug'=>$event->getSlug()]);
        }
        return $this->render('event/form.html.twig',[
            'form'=>$form->createView(),
            'title'=>$this->translator->trans("event.edit.text")
        ]);
    }

    /**
     * @Route("/{_locale}/event/{slug}", name="app_event_show")
     * @param Event $event
     * @param RoomRepository $repository
     * @return Response
     */
    public function show(Event $event,RoomRepository $repository): Response
    {
        if($event->getStatus()===0)
        {
            return $this->redirectToRoute('home');
        }
        $rooms=$repository->getEventRooms($event);
        return $this->render('event/show.html.twig',[
            'event'=>$event,
            'manage'=>false,
            'rooms'=>$rooms
        ]);
    }

    private function generateEventCodes(Event $event,int $count=5): array
    {
        $codes=array();
        for($i=0;$i<$count;$i++)
        {
            $code=new EventCode();
            $unique=substr(md5(uniqid()),0,6);
            $code->setEvent($event)->setName($event->getOrganizer().$unique);
            $codes[]=$code;
        }
        return $codes;
    }

    /**
     * @Route("/{_locale}/event/{id}/delete", name="app_event_delete", methods={"DELETE"})
     * @param Request $request
     * @param Event $event
     * @return Response
     */
    public function delete(Request $request, Event $event): Response
    {
        $user=$this->getUser();
        if($event->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($event);
            foreach ($event->getEventCodes() as $code)
            {
                $entityManager->remove($code);
            }
            $entityManager->flush();
            $filesystem = new Filesystem();
            $Logo=$this->getParameter('upload_image_directory').$event->getLogo();
            if($filesystem->exists($Logo))
            {
                $filesystem->remove($Logo);
            }
        }
        return $this->redirectToRoute('app_event_list');
    }

    /**
     * @Route("/{_locale}/manage/{slug}/open", name="app_event_open", methods={"PATCH"})
     * @param Event $event
     * @return RedirectResponse
     */
    public function openEvent(Event $event): RedirectResponse
    {
        $user=$this->getUser();
        if($event->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $em=$this->getDoctrine()->getManager();
        $newStatus=($event->getStatus()==0 ? 1 : ($event->getStatus()==1 ? 0 : 2 ));
        $event->setStatus($newStatus);
        $em->persist($event);
        $em->flush();
        $this->addFlash('success', $this->translator->trans($newStatus==1 ?'event.activated' : 'event.hidden'));
        return $this->redirectToRoute('app_manage');
    }

    /**
     * @Route("/{_locale}/manage/event{slug}/close", name="app_manage_close_event", methods={"PATCH"})
     * @param Event $event
     * @return RedirectResponse
     */
    public function closeEvent(Event $event): RedirectResponse
    {
        $user=$this->getUser();
        if($event->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $em=$this->getDoctrine()->getManager();
        $event->setStatus(2);
        $em->persist($event);
        $em->flush();

        $this->addFlash('success',$this->translator->trans('event.close.success'));
        return $this->redirectToRoute('app_manage');
    }

    /**
     * @Route("{_locale}/manage/event/{slug}/codes", name="app_event_codes_menage")
     * @param Event $event
     * @param Request $request
     * @return Response
     */
    public function manageCodes(Event $event,Request $request): Response
    {
        $user=$this->getUser();
        if($event->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $codes=$event->getEventCodes();
        $form=$this->createForm(AddCodesType::class,null);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $count=$form->get('count')->getData();
            $codes=$this->generateEventCodes($event,$count);
            foreach ($codes as $code)
            {
                $em->persist($code);
            }
            $em->flush();
            $this->addFlash('success',$this->translator->trans('codes.new.generated'));
            return  $this->redirectToRoute('app_event_codes_menage',['slug'=>$event->getSlug()]);
        }
        return $this->render('event/codes.html.twig',[
           'event'=>$event,
           'codes'=>$codes,
            'form'=>$form->createView()
        ]);
    }
}
