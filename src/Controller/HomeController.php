<?php

namespace App\Controller;

use App\Entity\Room;
use App\Form\NameType;
use App\Repository\EventRepository;
use App\Repository\PollingRepository;
use App\Repository\RoomRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function indexNoLocale()
    {
        return $this->redirectToRoute('app_event_list',array('_locale'=>'pl'));
    }

    /**
     * @Route("/{_locale}", name="app_event_list")
     * @param EventRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function index(EventRepository $repository,PaginatorInterface $paginator, Request $request)
    {
        return $this->render('event/index.html.twig', [
            'pagination' => $paginator->paginate(
                $repository->findAllOpen(),$request->query->getInt('page', 1),20,[]),
            'manage'=>false
        ]);
    }


    /**
     * @Route("/{_locale}/enter/", name="app_enter_front")
     * @param Request $request
     * @param RoomRepository $roomRepository
     * @param PollingRepository $pollingRepository
     */
    public function enterFromFront(Request $request,RoomRepository $roomRepository,PollingRepository $pollingRepository)
    {
        $code=$request->request->get('event_code');
        //$code="Przemysław858512";
        $session=$request->getSession();
        if(is_null($code))
        {
            $code=$session->get('event_code_post');
            if(is_null($code))
            return $this->redirectToRoute('home');
        }else{
            $session->set('event_code_post',$code);
        }
        $now= new \DateTime();
        $rooms=$roomRepository->findRoomsWithCode($code);
        $pollings=$pollingRepository->findPollingsWithCode($code,$now);
        //dd($rooms,$pollings);
        $findR=sizeof($rooms);
        $findP=sizeof($pollings);
        $find=$findR+$findP;
        $form=$this->createForm(NameType::class,null);
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            $name=$form->get('name')->getData();
            if(mb_strlen($name)<5)
            {
                $form->get('name')->addError(new FormError('Imię powinno mieć conajmniej 5 znaków'));
            }
            if($form->isValid())
            {
                $session->set('name',$name);
            }
        }
        if($find>0)
        {
            $name=$session->get('name');
            if(is_null($name))
            {
                return $this->render('home/nameForm.html.twig',[
                        'form'=>$form->createView()
                    ]
                );
            }
            foreach ($rooms as $room)
            {
                /**
                 * @var $room Room
                 */
                $session->set('room_'.$room->getId().'_code',$code);
            }
            foreach ($pollings as $polling)
            {
                $session->set('polling_'.$polling->getId().'_code',$code);
            }
            $this->addFlash('success','Gratulacje, wprowadziłeś poprawny kod dostępu');
            if($find>1)
            {
                return $this->render('home/enter.html.twig',[
                    'rooms'=>$rooms,
                    'pollings'=>$pollings
                ]);
            }else{
                if($findR===1)
                {
                    $room=$rooms[0];
                    return $this->redirectToRoute('app_room_enter',[
                       'slug_child'=>$room->getSlug(),
                       'slug_parent'=>$room->getEvent()->getSlug()
                    ]);
                }else{
                    $polling=$pollings[0];
                    return $this->redirectToRoute('app_polling_enter',[
                       'slug'=>$polling->getSlug()
                    ]);
                }
            }
        }else{
            return $this->redirect('http://gpvoting.gpedulife.pl/gpvoting-front/?error=bad_code');
        }

    }
}
