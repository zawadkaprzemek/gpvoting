<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\LoginFormAuthenticator;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{
    private EmailVerifier $emailVerifier;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    public function __construct(EmailVerifier $emailVerifier, TranslatorInterface $translator)
    {
        $this->emailVerifier = $emailVerifier;
        $this->translator = $translator;
    }

    /**
     * @Route("/register", name="app_register")
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordEncoder
     * @return Response
     */
    public function register(Request $request, UserPasswordHasherInterface $passwordEncoder): Response
    {
        if($this->isGranted("ROLE_USER"))
        {
            return $this->redirectToRoute('home');
        }
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            )
                ->setRoles(['ROLE_USER', 'ROLE_ORGANIZATOR']);

            $entityManager = $this->getDoctrine()->getManager();
            $pack=$entityManager->getRepository('App:Pack')->find(1);
            $user->setPack($pack);
            $user->setLastIP($request->getClientIp());
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('kontakt@gpvoting.pl', 'GpVoting'))
                    ->to($user->getEmail())
                    ->subject($this->translator->trans('register.form.verify_email.text'))
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     * @param Request $request
     * @return Response
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }
        $this->addFlash('success', $this->translator->trans('register.form.verify_email.success'));

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/admin/create_account", name="app_admin_create_account")
     * @param Request $request
     * @param UserPasswordHasherInterface $passwordEncoder
     * @return Response
     */
    public function createAccount(Request $request, UserPasswordHasherInterface $passwordEncoder): Response
    {
        $this->denyAccessUnlessGranted("ROLE_ADMIN");
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
                ->setRoles(['ROLE_USER', 'ROLE_ORGANIZATOR']);

            $entityManager = $this->getDoctrine()->getManager();
            $user->setLastIP($request->getClientIp());
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('kontakt@gpvoting.pl', 'GpVoting'))
                    ->to($user->getEmail())
                    ->subject($this->translator->trans('register.form.email.account_created.subject'))
                    ->htmlTemplate('registration/create_account_email.html.twig')
                    ->context(['password' => $form->get('plainPassword')->getData()])
            );
            // do anything else you need here, like send an email
            $this->addFlash('success',$this->translator->trans("profile.create.success"));
            return $this->redirectToRoute('home');
        }
        return $this->render('registration/create_account.html.twig', [
            'form' => $form->createView(),
            'button_text'=>$this->translator->trans('profile.create.button'),
            'title'=>$this->translator->trans('profile.create.text')
        ]);
    }
}
