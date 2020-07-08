<?php

namespace App\Controller;

use App\Entity\Event;
use App\Entity\Room;
use App\Form\RoomEnterType;
use App\Form\RoomType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class RoomController extends AbstractController
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
     * @Route("/{_locale}/manage/{slug}/create_room", name="app_manage_add_room")
     * @param Event $event
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addRoom(Event $event,Request $request)
    {
        $user=$this->getUser();
        if($event->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $room=new Room();
        $room->setEvent($event);
        $form=$this->createForm(RoomType::class,$room);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($room);
            $em->flush();
            $this->addFlash('sucess','Dodano nowy pokój');
            return $this->redirectToRoute('app_manage_event_show',['slug'=>$event->getSlug()]);
        }

        return $this->render('room/form.html.twig',[
            'form'=>$form->createView(),
            'title'=>$this->translator->trans('Dodaj pokój')
        ]);
    }

    /**
     * @Route("/{_locale}/manage/{slug_parent}/{slug_child}/edit", name="app_manage_edit_room")
     * @ParamConverter("event", options={"mapping": {"slug_parent": "slug"}})
     * @ParamConverter("room", options={"mapping": {"slug_child": "slug"}})
     * @param Event $event
     * @param Room $room
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function editRoom(Event $event,Room $room,Request $request)
    {
        $user=$this->getUser();
        if($event->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $form=$this->createForm(RoomType::class,$room);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($room);
            $em->flush();
            $this->addFlash('success','Zapisano zmiany');
            return $this->redirectToRoute('app_manage_event_show',['slug'=>$event->getSlug()]);
        }

        return $this->render('room/form.html.twig',[
            'form'=>$form->createView(),
            'title'=>$this->translator->trans('Edytuj pokój')
        ]);
    }

    /**
     * @Route("/{_locale}/event/{slug_parent}/room/{slug_child}/show", name="app_room_show")
     * @ParamConverter("event", options={"mapping": {"slug_parent": "slug"}})
     * @ParamConverter("room", options={"mapping": {"slug_child": "slug"}})
     * @param Event $event
     * @param Room $room
     * @return Response
     */
    public function showRoom(Event $event,Room $room)
    {
        $pollings=[];
        $now=new \DateTime();
        foreach ($room->getPollings() as $polling)
        {
            if(sizeof($polling->getQuestions())==$polling->getQuestionsCount())
            if($polling->getSession())
            {
                $pollings[]=$polling;
            }else{
                if($polling->getStartDate()<$now &&$polling->getEndDate()>$now)
                {
                    $pollings[]=$polling;
                }
            }
        }
        return $this->render('room/index.html.twig',[
           'room'=>$room,
            'manage'=>false,
            'pollings'=>$pollings
        ]);
    }

    /**
     * @Route("/{_locale}/room/{slug_parent}/{slug_child}/enter", name="app_room_enter")
     * @ParamConverter("event", options={"mapping": {"slug_parent": "slug"}})
     * @ParamConverter("room", options={"mapping": {"slug_child": "slug"}})
     * @param Room $room
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function enter(Room $room,Request $request)
    {
        $session=$request->getSession();
        $enter=$session->get('room_'.$room->getId().'_code');
        if(!is_null($enter))
        {
            return $this->redirectToRoute('app_room_show',[
                'slug_parent'=>$room->getEvent()->getSlug(),
                'slug_child'=>$room->getSlug()
            ]);
        }
        $form=$this->createForm(RoomEnterType::class,null);
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            $code=$form->get('code')->getData();
            $name=$form->get('name')->getData();
            if($code!==$room->getCode()->getName())
            {
                $form->get('code')->addError(new FormError('Nie poprawny kod'));
            }
            if($form->isValid())
            {
                $session->set('room_'.$room->getId().'_code',$code);
                $session->set('name',$name);
                return $this->redirectToRoute('app_room_show',[
                    'slug_parent'=>$room->getEvent()->getSlug(),
                    'slug_child'=>$room->getSlug()
                ]);
            }
        }

        return $this->render('room/enter.html.twig',[
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/{_locale}/manage/room/{id}/delete", name="app_room_delete", methods={"DELETE"})
     * @param Room $room
     * @param Request $request
     * @return Response
     */
    public function deleteRoom(Room $room,Request $request)
    {
        $user=$this->getUser();
        if($room->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        if ($this->isCsrfTokenValid('delete'.$room->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($room);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_manage_event_show',['slug'=>$room->getEvent()->getSlug()]);
    }

    /**
     * @Route("{_locale}/manage/room/{slug}/visible", name="app_room_visible", methods={"PATCH"})
     * @param Room $room
     * @return RedirectResponse
     */
    public function visibleRoom(Room $room)
    {
        $user=$this->getUser();
        if($room->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $em=$this->getDoctrine()->getManager();
        $room->setVisible(!$room->getVisible());
        $em->persist($room);
        $em->flush();
        $this->addFlash('success',($room->getVisible()? 'Odkryto pokój' : 'Ukryto pokój'));
        return $this->redirectToRoute('app_manage_event_show',['slug'=>$room->getEvent()->getSlug()]);
    }
}
