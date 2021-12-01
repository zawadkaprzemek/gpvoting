<?php

namespace App\Controller;

use App\Entity\Pack;
use App\Entity\User;
use App\Form\PackType;
use App\Form\ProfileEditAdminType;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class AdminController
 * @package App\Controller
 * @IsGranted("ROLE_ADMIN")
 * @Route("/admin")
 */
class AdminController extends AbstractController
{

    private TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="admin_index")
     */
    public function adminAction(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    /**
     * @Route("/users", name="admin_users_list")
     */
    public function index(UserRepository $repository,PaginatorInterface $paginator, Request $request): Response
    {
        return $this->render('admin/users.html.twig', [
            'pagination' => $paginator->paginate(
                $repository->findAll(),$request->query->getInt('page', 1),20,[])
        ]);
    }

    /**
     * @Route("/packs", name="admin_packs_list")
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */
    public function packListAction(PaginatorInterface $paginator,Request $request): Response
    {
        $repository=$this->getDoctrine()->getManager()->getRepository('App:Pack');
        return $this->render('admin/packs.html.twig', [
            'pagination' => $paginator->paginate(
                $repository->findAll(),$request->query->getInt('page', 1),20,[])
        ]);
    }

    /**
     * @Route("/packs/new", name="admin_packs_new")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function createPackAction(Request $request):Response
    {
        $pack=new Pack();
        $form=$this->createForm(PackType::class,$pack);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($pack);
            $em->flush();

            $this->addFlash('success',$this->translator->trans('packs.new.success'));
            return $this->redirectToRoute('admin_packs_list');
        }

        return $this->render('admin/packs_form.html.twig', array(
           'form' => $form->createView(),
            'title'=>$this->translator->trans('packs.new.title')
        ));
    }

    /**
     * @Route("/packs/{id}/edit", name="admin_packs_edit")
     * @param Request $request
     * @param Pack $pack
     * @return RedirectResponse|Response
     */
    public function editPackAction(Request $request,Pack $pack):Response
    {
        $form=$this->createForm(PackType::class,$pack);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($pack);
            $em->flush();

            $this->addFlash('success',$this->translator->trans('packs.edit.success'));
            return $this->redirectToRoute('admin_packs_list');
        }

        return $this->render('admin/packs_form.html.twig', array(
            'form' => $form->createView(),
            'title'=>$this->translator->trans('packs.edit.title')
        ));
    }

    /**
     * @Route("/users/{id}/show", name="app_admin_user_show")
     */
    public function userShow(User $user): Response
    {
        return $this->render('admin/show.html.twig',[
            'user' => $user
        ]);
    }


    /**
     * @Route("/users/{id}/edit", name="app_admin_profile_edit")
     * @param User $user
     * @param Request $request
     * @return Response
     */
    public function userEdit(User $user,Request $request): Response
    {
        $form=$this->createForm(ProfileEditAdminType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success',$this->translator->trans('changes_saved'));
            return $this->redirectToRoute('app_admin_user_show',['id'=>$user->getId()]);
        }

        return $this->render('admin/edit.html.twig',[
            'form'=>$form->createView(),
            'user'=>$user
        ]);
    }
}
