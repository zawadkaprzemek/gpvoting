<?php

namespace App\Controller;

use App\Entity\ParticipantList;
use App\Entity\User;
use App\Form\ImportParticipantsType;
use App\Form\ParticipantListType;
use App\Repository\ParticipantListRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
            dd($form->getData());
        }

        return $this->render('participant/import.html.twig',[
            'form'=>$form->createView(),
            'list'=>$list
        ]);
    }
}
