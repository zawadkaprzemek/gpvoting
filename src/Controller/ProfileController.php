<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\PasswordChangeType;
use App\Form\RegistrationFormType;
use App\Form\UserEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @IsGranted("ROLE_USER")
 * @Route("/{_locale}/profile")
 * Class ProfileController
 * @package App\Controller
 */
class ProfileController extends AbstractController
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @Route("/", name="app_profile")
     */
    public function index()
    {
        return $this->render('profile/show.html.twig', [
            'user'=>$this->getUser()
        ]);
    }

    /**
     * @Route("/edit", name="app_profile_edit")
     * @param Request $request
     * @return Response
     */
    public function edit(Request $request)
    {
        $user=$this->getUser();
        $form=$this->createForm(UserEditType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted()&&$form->isValid())
        {
            $em=$this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success','Zapisano zmiany');
            return $this->redirectToRoute('app_profile');
        }

        return $this->render('profile/edit.html.twig',[
           'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/change_password", name="app_profile_change_password")
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordEncoder
     * @return RedirectResponse|Response
     */
    public function changePassword(Request $request,UserPasswordHasherInterface $passwordEncoder)
    {
        /** @var User $user */
        $user=$this->getUser();
        $form=$this->createForm(PasswordChangeType::class,$user);
        $form->handleRequest($request);
        if($form->isSubmitted())
        {
            if(!$passwordEncoder->isPasswordValid($user,$form->get('oldPassword')->getData())){
                $form->get('oldPassword')->addError(new FormError('Podano nie prawidłowe hasło'));
            }
            if($form->isValid())
            {
                $newpassword=$passwordEncoder->hashPassword($user, $form->get('plainPassword')->getData());
                $user->setPassword($newpassword);
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                $this->addFlash('success', 'Zmiana hasła zakończona powodzeniem');
                return $this->redirectToRoute('app_profile');
            }
        }

        return $this->render('profile/change_password.html.twig',[
           'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/create_subaccount", name="app_profile_create_subacccount")
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordEncoder
     * @return Response
     */
    public function createSubAccount(Request $request, UserPasswordHasherInterface $passwordEncoder): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ORGANIZATOR");
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->remove('agreeTerms');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            )
                ->setRoles(['ROLE_USER', 'ROLE_SUBACCOUNT']);
            $user->setParent($user);
            $user->setIsVerified(true);
            $entityManager = $this->getDoctrine()->getManager();
            $user->setLastIP($request->getClientIp());
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success',$this->translator->trans("Konto zostało utworzone"));
            return $this->redirectToRoute('app_profile');
        }
        return $this->render('registration/create_account.html.twig', [
            'form' => $form->createView(),
            'button_text'=>$this->translator->trans('Stwórz podkonto'),
            'title'=>$this->translator->trans('Utwórz podkonto')
        ]);
    }
}
