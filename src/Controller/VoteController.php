<?php

namespace App\Controller;

use App\Entity\Polling;
use App\Entity\Question;
use App\Entity\SessionUsers;
use App\Entity\Vote;
use App\Form\NameType;
use App\Form\VoteType;
use App\Repository\QuestionRepository;
use App\Repository\SessionSettingsRepository;
use App\Repository\SessionUsersRepository;
use App\Repository\VoteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VoteController extends AbstractController
{
    /**
     * @Route("/polling/{slug}/vote", name="app_polling_vote")
     * @param Polling $polling
     * @param Request $request
     * @param QuestionRepository $repository
     * @return Response
     * @throws \Exception
     */
    public function index(Polling $polling,Request $request,QuestionRepository $repository)
    {
        $session=$request->getSession();
        $enter=$session->get('room_'.$polling->getRoom()->getId().'_code');
        $test=$session->get('polling_'.$polling->getId().'_test');
        $test=(is_null($test)? false : $test=== true);
        if($polling->getSession()&&!$test)
        {
            return $this->redirectToRoute('app_polling_vote_session',['slug'=>$polling->getSlug()]);
        }
        if(is_null($enter)&&!$test)
        {
            return $this->redirectToRoute('app_room_enter',[
                'slug_parent'=>$polling->getRoom()->getEvent()->getSlug(),
                'slug_child'=>$polling->getRoom()->getSlug()
            ]);
        }
        $cookie=($test ? $request->cookies->get("vote_{$polling->getId()}_test") : $request->cookies->get("vote_{$polling->getId()}"));
        $questions=$repository->getPollingQuestions($polling);
        if(is_null($cookie))
        {
            /**
             * Jesli nie ma ciasteczka to wczytujemy pierwsze pytanie
             */
            $question=$questions[0];
            if($test)
            {
                $session->set('vote_'.$polling->getId().'_start_test',new \DateTime());
            }else{
                $session->set('vote_'.$polling->getId().'_start',new \DateTime());
            }
        }else{
            /**
             * Jeśli ciasteczko istnieje to zliczamy ilość odpowiedzi z ciastka i pokazujemy następne pytanie
             */
            $arr=explode(",",$cookie);
            if(sizeof($arr)>=sizeof($questions))
            {
                if($test)
                {
                    if(is_null($session->get('vote_'.$polling->getId().'_end_test')))
                    {
                        $session->set('vote_'.$polling->getId().'_end_test',new \DateTime());
                    }
                }else{
                    if(is_null($session->get('vote_'.$polling->getId().'_end')))
                    {
                        $session->set('vote_'.$polling->getId().'_end',new \DateTime());
                    }

                }
                return $this->redirectToRoute('app_polling_vote_end',['slug'=>$polling->getSlug()]);
            }
            $question=$questions[sizeof($arr)];
        }
        /**
         * @var $question Question
         */
        $vote=new Vote();
        $vote->setQuestion($question)->setStartDate(new \DateTime());
        $form=$this->createForm(VoteType::class,$vote);
        $form->handleRequest($request);
        $answer=$request->request->get('answer');
        if($form->isSubmitted() &&$form->isValid()&& !is_null($answer))
        {
            $vote
                ->setAddressIP($request->getClientIp())
                ->setStartDate(new \DateTime($form['startDateString']->getData()))
                ->setEndDate(new \DateTime())
                ->setWho(($test ? 'Test' :($session->get('name')?? 'guest')))
                ->setTest($test)
            ;
            $diff=$vote->getEndDate()->diff($vote->getStartDate());
            $seconds=$diff->i*60+$diff->s+$diff->f;
            $vote->setAnswerTime($seconds);
            $answers=[];
            if(is_iterable($answer))
            {
                foreach ($answer as $item)
                {
                    $answers[]=$item;
                }
            }else{
                $answers[]=$answer;
            }
            $vote->setAnswers($answers);

            $val=(is_null($cookie)? $question->getId() : $cookie.",".$question->getId());
            $cookie=new Cookie(
                ($test? "vote_{$polling->getId()}_test" :"vote_{$polling->getId()}"),
                $val,strtotime('tomorrow'),
                "/"
            );
            $em=$this->getDoctrine()->getManager();
            $em->persist($vote);
            $em->flush();
            $response=$this->redirectToRoute('app_polling_vote',['slug'=>$polling->getSlug()]);
            $response->headers->setCookie($cookie);
            return $response;
        }
        return $this->render('polling/vote.html.twig', [
            'question' => $question,
            'polling'=>$polling,
            'form'=>$form->createView()
        ]);
    }

    /**
     * @Route("/polling/{slug}/vote_test", name="app_polling_vote_test")
     * @param Polling $polling
     * @param Request $request
     * @return RedirectResponse
     */
    public function testVote(Polling $polling,Request $request)
    {
        $user=$this->getUser();
        if($polling->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $session=$request->getSession();
        $session->set('polling_'.$polling->getId().'_test',true);
        return $this->redirectToRoute('app_polling_vote',['slug'=>$polling->getSlug()]);
    }

    /**
     * @Route("/polling/{slug}/end", name="app_polling_vote_end")
     * @param Polling $polling
     * @param Request $request
     * @param VoteRepository $repository
     * @return Response
     */
    public function voteEnd(Polling $polling,Request $request,VoteRepository $repository)
    {
        $user=$this->getUser();
        if($polling->getRoom()->getEvent()->getUser()!==$user)
        {
            return $this->redirectToRoute('home');
        }
        $session=$request->getSession();
        $test=$session->get('polling_'.$polling->getId().'_test');
        $test=(is_null($test)? false : $test=== true);
        $start=$session->get('vote_'.$polling->getId().'_start'.($test? '_test': ''));
        $end=$session->get('vote_'.$polling->getId().'_end'.($test? '_test': ''));
        $session->remove('polling_'.$polling->getId().'_test');
        /**
         * @var $end \DateTime
         */
        $diff=$end->diff($start);
        return $this->render('vote/end.html.twig',[
            'polling'=>$polling,
            'diff'=>$diff->format("%i minut %s.%f sekund")
        ]);
    }

    /**
     * @Route("/vote/{id}/delete", name="app_vote_delete", methods={"DELETE"})
     * @param Vote $vote
     * @param Request $request
     * @return RedirectResponse
     */
    public function deleteVote(Vote $vote,Request $request)
    {
        $user=$this->getUser();
        if($vote->getQuestion()->getPolling()->getRoom()->getEvent()->getUser!==$user)
        {
            return $this->redirectToRoute('home');
        }
        if ($this->isCsrfTokenValid('delete'.$vote->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($vote);
            $entityManager->flush();
        }
        return $this->redirectToRoute('app_question_results',[
            'slug'=>$vote->getQuestion()->getPolling()->getSlug(),
            'sort'=>$vote->getQuestion()->getSort()
            ]);
    }

    /**
     * @Route("/polling/{slug}/vote_session", name="app_polling_vote_session")
     * @param Polling $polling
     * @param Request $request
     * @param SessionSettingsRepository $repository
     * @param SessionUsersRepository $sessionUsersRepository
     * @param QuestionRepository $questionRepository
     * @return Response
     * @throws \Exception
     */
    public function voteSession(Polling $polling,Request $request,
                                SessionSettingsRepository $repository,SessionUsersRepository $sessionUsersRepository,
                                QuestionRepository $questionRepository): Response
    {
        if(!$polling->getRoom()->getVisible())
        {
            return $this->redirectToRoute('home');
        }
        $session=$request->getSession();
        $name=$session->get('name');

        if($name===null)
        {
            $nameForm=$this->createForm(NameType::class);
            $nameForm->handleRequest($request);
            if($nameForm->isSubmitted()&&$nameForm->isValid())
            {
                $data=$nameForm->getData();
                $session->set('name',$data['name']);
                $name=$session->get('name');
            }else{
                return $this->render('polling/nameForm.html.twig',[
                   'form'=>$nameForm->createView()
                ]);
            }
        }
        $ip=$request->getClientIp();
        $em=$this->getDoctrine()->getManager();
        $settings=$repository->getSessionSettings($polling);
        $cookie=$request->cookies->get("vote_{$polling->getId()}");
        if(is_null($settings))
        {
            $activeQ=1;
            $status=0;
        }else{
            $activeQ=$settings->getActiveQuestion();
            $status=$settings->getStatus();
        }
        $vote=new Vote();
        $question=$questionRepository->findQuestion($polling,$activeQ);
        $vote->setQuestion($question)->setStartDate(new \DateTime());
        $form=$this->createForm(VoteType::class,$vote);
        $form->handleRequest($request);
        $answer=$request->request->get('answer');
        if($form->isSubmitted() &&$form->isValid()&& !is_null($answer)) {
            $vote
                ->setAddressIP($request->getClientIp())
                ->setStartDate(new \DateTime($form['startDateString']->getData()))
                ->setEndDate(new \DateTime())
                ->setWho($name)
            ;
            $diff = $vote->getEndDate()->diff($vote->getStartDate());
            $seconds = $diff->i * 60 + $diff->s + $diff->f;
            $vote->setAnswerTime($seconds);
            $answers = [];
            if (is_iterable($answer)) {
                foreach ($answer as $item) {
                    $answers[] = $item;
                }
            } else {
                $answers[] = $answer;
            }
            $vote->setAnswers($answers);

            $val = (is_null($cookie) ? $question->getId() : $cookie . "," . $question->getId());
            $cookie = new Cookie(
                "vote_{$polling->getId()}",
                $val, strtotime('tomorrow'),
                "/"
            );
            $em->persist($vote);
            $em->flush();
            $response = $this->redirectToRoute('app_polling_vote_session', ['slug' => $polling->getSlug()]);
            $response->headers->setCookie($cookie);
            return $response;
        }
        $sessionUser=$sessionUsersRepository->checkUserOnTheList($name,$ip,$polling);
        if(is_null($sessionUser))
        {
            $sessionUser=new SessionUsers();
            $sessionUser->setName($name)
                        ->setPolling($polling)
                        ->setIp($ip);
        }
        $sessionUser->setDate(new \DateTime());

        $em->persist($sessionUser);
        $em->flush();

        $now=new \DateTime();
        /*
         * 4 typy statusów głosowania sesyjnego
         * 0 - nieaktywne
         * 1 - aktywne
         * 2 - zakończone
         * 3 - aktywne, oczekiwanie na rozpoczęcie kolejnego pytania przez organizatora
         */
        if($status===0)
        {
            return $this->render('vote/session.html.twig',[
                'polling'=>$polling,
                'seconds'=>30,
                'status'=>$status
            ]);
        }elseif ($status==1)
        {
            $cookieArr=explode(",",$cookie);
            if($now<$settings->getAnswerStart())
            {
                $diff=$now->diff($settings->getAnswerStart());
                return $this->render('vote/session.html.twig',[
                    'polling'=>$polling,
                    'seconds'=>$diff->s,
                    'status'=>$status
                ]);
            }else{
                if($now>=$settings->getAnswerEnd()||in_array($question->getId(),$cookieArr))
                {
                    if(in_array($question->getId(),$cookieArr))
                    {
                        $results=$this->getQuestionVotesResults($question);
                    }else{
                        $results=null;
                    }
                    return $this->render('vote/session.html.twig',[
                       'polling'=>$polling,
                       'seconds'=>30,
                        'status'=>3,
                        'results'=>$results,
                        'question'=>$question
                    ]);
                }else{

                    $diff=$now->diff($settings->getAnswerEnd());
                    $seconds=$diff->i*60+$diff->s;
                    return $this->render('polling/vote.html.twig',[
                        'polling'=>$polling,
                        'question'=>$question,
                        'seconds'=>$seconds,
                        'form'=>$form->createView()
                    ]);
                }
            }
        }else{
            return $this->render('vote/session.html.twig',[
               'polling'=>$polling,
               'status'=>2,
                'seconds'=>null
            ]);
        }


    }

    private function getQuestionVotesResults(Question $question)
    {
        $em=$this->getDoctrine()->getManager();
        /**
         * @var $repository VoteRepository
         */
        $repository=$em->getRepository(Vote::class);
        $votes=$repository->getQuestionVotes($question);
        $results=[];
        $votesArr=[];
        foreach ($question->getAnswers() as $answer)
        {
            $results[$answer->getId()]=array(
                'content'=>$answer->getContent(),
                'valid'=>$answer->isValid(),
                'count'=>0
            );
        }
        $sum=0;
        foreach ($votes as $key=> $vote) {

            foreach ($vote->getAnswers() as $answer)
            {
                $results[$answer]['count']++;
                $sum++;
            }
        }
        foreach ($results as $key=>$result)
        {
            $results[$key]['percent']=round(($result['count']/$sum)*100,2);
        }

        return $results;
    }
}
