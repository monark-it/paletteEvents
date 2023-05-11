<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use App\Repository\TicketRepository;
use App\Repository\UserRepository;
use App\Service\StripeService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends BaseController
{
    #[Route('/admin/users', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
        ]);
    }

    #[Route('/admin/users/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, UserRepository $userRepository): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/users/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, UserRepository $userRepository): Response
    {
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($user, true);

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/admin/users/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $userRepository->remove($user, true);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/animateurs', name: 'animators', methods: ['GET'])]
    public function showAnimators(UserRepository $userRepository){
        $animators = $userRepository->findAnimateurs();
        return $this->render('user/allAnimators.html.twig',[
            'animators' => $animators
        ]);
    }

    #[Route('/profile/{fullName}', name: 'profile', methods: ['GET','POST'])]
    public function profile($fullName,Request $request,EntityManagerInterface $manager,UserRepository $userRepository,StripeService $stripeService,TicketRepository $ticketRepository){
        $user=$this->getUser();
        if(!$user) return $this->redirectToRoute("app_login");
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $role=($manager->getRepository(Role::class)->roleByUser($user));
            if($role) $role=$role[0];
            $userRepository->update($user,$form->getData(),$role);
            $manager->persist($user);
            $manager->flush();
            return $this->redirectToRoute('profile',['fullName'=>$user->getFullName()]);
        }
        $tickets=$ticketRepository->findBy(['user'=>$user]);
        $factures=[];
        foreach ($tickets as $ticket){
            $factures[]=[
                'event'=>$ticket->getPrix()->getEvent()->getTitle(),
                'price'=>$ticket->getPrix()->getSomme(),
                'date'=>$ticket->getFacture()->getCreatedAt(),
                'status'=>$ticket->getFacture()->getStatus(),
                'receipt'=>$ticket->getFacture()->getCode()
            ];
        }
        return $this->render("user/profile.html.twig",[
            'form' => $form->createView(),
            'factures' => $factures,
        ]);
    }
}
