<?php

namespace App\Controller;

use App\Entity\Polling;
use App\Entity\Room;
use App\Form\EnterType;
use App\Form\NameType;
use App\Repository\EventRepository;
use App\Repository\PollingRepository;
use App\Repository\RoomRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class HomeController extends AbstractController
{
    private TranslatorInterface $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator){
        $this->translator = $translator;
    }

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
     * @return RedirectResponse|Response
     */
    public function enterFromFront(Request $request,RoomRepository $roomRepository,PollingRepository $pollingRepository)
    {
        $requestData=$request->request->all();

        $code=$requestData['event_code'] ?? ($requestData['enter']['event_code'] ?? null);
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
                $form->get('name')->addError(new FormError($this->translator->trans('form.error.name_short',['%limit%'=>5])));
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
                /** @var Polling $polling */
                $session->set('polling_'.$polling->getId().'_code',$code);
                /** @var Room $room */
                $room=$polling->getRoom();
                $session->set('room_'.$room->getId().'_code',$room->getCode());
            }
            $this->addFlash('success',$this->translator->trans('codes.valid_code_enter'));
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
            return $this->redirect($request->headers->get('referer').'?error=bad_code');
        }
    }

    /**
     * @Route("/{_locale}/enter_form_test", name="enter_form_test")
     * @param Request $request
     * @return Response
     */
    public function testFrontEnterAction(Request $request):Response
    {
        $session=$request->getSession();
        $code=$session->get('event_code_post');

        $form=$this->createForm(EnterType::class,null);

        if($request->query->get('error'))
        {
            $this->addFlash('warning',$this->translator->trans('codes.bad_code_enter'));
        }

        return $this->render('home/enter_form_test.html.twig',[
           'form'=>$form->createView(),
           'code'=>$code ,
        ]);

    }

    /**
     * @Route("/{_locale}/clear_session_codes", name="app_clear_session_codes")
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteSessionAction(Request $request): RedirectResponse
    {
        $session=$request->getSession();
        $keys=$session->all();
        foreach ($keys as $key=>$value) {
            if(substr($key,0,1)!=='_')
            {
                $session->remove($key);
            }
        }
        $this->addFlash('success',$this->translator->trans('codes.session_cleared'));
        return $this->redirectToRoute('enter_form_test');
    }
}
