<?php

namespace App\Controller;

use App\Entity\Participant;
use App\Entity\ParticipantList;
use App\Entity\User;
use App\Form\ImportParticipantsType;
use App\Form\ParticipantListType;
use App\Form\ParticipantType;
use App\Repository\ParticipantListRepository;
use App\Repository\ParticipantRepository;
use SimpleXLSX;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParticipantController extends AbstractController
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
     * @Route("/{_locale}/manage/participants_lists/show", name="app_manage_participants_lists")
     * @param ParticipantListRepository $repository
     * @return Response
     */
    public function index(ParticipantListRepository $repository)
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
     * @Route("/{_locale}/manage/participant_lists/new", name="app_manage_participant_list_new")
     * @param Request $request
     * @return Response
     */
    public function new(Request $request)
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
            $this->addFlash('success',$this->translator->trans('Stworzono nową listę uczestników'));
            return $this->redirectToRoute('app_manage_participants_lists');
        }

        return $this->render('participant/lists_form.html.twig',[
           'form'=>$form->createView(),
            'title'=>$this->translator->trans('Utwórz listę uczestników')
        ]);
    }

    /**
     * @Route("/{_locale}/manage/participants_lists/{id}/edit", name="app_manage_participant_list_edit")
     * @param ParticipantList $list
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function edit(ParticipantList $list,Request $request)
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
            $this->addFlash('success',$this->translator->trans('Edycja listy uczestników zakończona sukcesem'));
            return $this->redirectToRoute('app_manage_participants_lists');
        }

        return $this->render('participant/lists_form.html.twig',[
            'form'=>$form->createView(),
            'title'=>$this->translator->trans('Edytuj listę uczestników')
        ]);
    }

    /**
     * @Route("/{_locale}/manage/participants_lists/{id}/show", name="app_manage_participant_list_show")
     * @param ParticipantList $list
     * @return RedirectResponse|Response
     */
    public function show(ParticipantList $list)
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
     * @Route("/{_locale}/manage/participants_lists/{id}/participants", name="app_manage_participant_list_show_participants")
     * @param ParticipantList $list
     * @param ParticipantRepository $repository
     * @return RedirectResponse|Response
     */
    public function showParticipants(ParticipantList $list,ParticipantRepository $repository)
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
     * @Route("/{_locale}/manage/participants_lists/{id}/import", name="app_manage_participant_list_import")
     * @param ParticipantList $list
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function importParticipants(ParticipantList $list,Request $request)
    {
        if($list->getUser()!==$this->getUser())
        {
            return $this->redirectToRoute('app_manage');
        }

        $form=$this->createForm(ImportParticipantsType::class);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $file=$form->getData()['file'];
            $xlsx=SimpleXLSX::parse($file);
            $em=$this->getDoctrine()->getManager();
            foreach ($xlsx->rows() as $row)
            {
                $participant=new Participant();
                $participant
                    ->setName($row[0])
                    ->setSurname($row[1])
                    ->setPlainPass($row[2])
                    ->setPassword(md5($row[2]))
                    ->setPhone($row[3])
                    ->setVotes($row[4])
                    ->setActions($row[5])
                    ->setList($list)
                ;
                $em->persist($participant);
            }
            $em->flush();
            $this->addFlash('success',$this->translator->trans('Import uczestników zakończony sukcesem'));
            return $this->redirectToRoute('app_manage_participant_list_show',['id'=>$list->getId()]);
        }

        return $this->render('participant/import.html.twig',[
            'form'=>$form->createView(),
            'list'=>$list
        ]);
    }

    /**
     * @Route("/{_locale}/assign_to_list/{hashId}/", name="app_participant_list_assign")
     * @param ParticipantList $list
     * @param Request $request
     * @return Response
     */
    public function signToList(ParticipantList $list,Request $request)
    {
        $participant=new Participant();
        $participant->setList($list);
        $form=$this->createForm(ParticipantType::class,$participant);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
        }

        return $this->render('participant/assign_form.html.twig',[
           'form'=>$form->createView(),
           'list'=>$list
        ]);
    }

    /**
     * @Route("/{_locale}/ajax/participant_list/{id}/open", name="app_participant_list_open", methods={"POST"})
     * @param ParticipantList $list
     * @param Request $request
     * @return JsonResponse
     */
    public function openClose(ParticipantList $list,Request $request)
    {
        if($list->getUser()!==$this->getUser())
        {
            return new JsonResponse(['status'=>'fail']);
        }
        $list->setOpen(!$list->getOpen());
        $em=$this->getDoctrine()->getManager();
        $em->persist($list);
        $em->flush();
        return new JsonResponse(['status'=>'success']);
    }
}
