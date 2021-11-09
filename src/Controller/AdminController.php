<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\ProfileEditAdminType;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 * @package App\Controller
 * @IsGranted("ROLE_ADMIN")
 * @Route("/admin")
 */
class AdminController extends AbstractController
{
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
     * @Route("/users/{id}/show", name="app_admin_user_show")
     */
    public function userShow(User $user)
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
    public function userEdit(User $user,Request $request)
    {
        $form=$this->createForm(ProfileEditAdminType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success','Zapisano zmiany');
            return $this->redirectToRoute('app_admin_user_show',['id'=>$user->getId()]);
        }

        return $this->render('admin/edit.html.twig',[
            'form'=>$form->createView(),
            'user'=>$user
        ]);
    }
}
