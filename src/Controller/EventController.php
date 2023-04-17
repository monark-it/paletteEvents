<?php

namespace App\Controller;

use App\Entity\Event;
use App\Form\EventType;
use App\Repository\CategoryRepository;
use App\Repository\EventRepository;
use App\Repository\TagRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends AbstractController
{
    #[Route('/admin/event', name: 'admin_events_index', methods: ['GET'])]
    public function adminIndex(EventRepository $eventRepository): Response
    {
        return $this->render('event/admin/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    #[Route('/event', name: 'events_index', methods: ['GET'])]
    public function index(EventRepository $eventRepository): Response
    {
        return $this->render('event/index.html.twig', [
            'events' => $eventRepository->findAll(),
        ]);
    }

    #[Route('/admin/event/new', name: 'app_event_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EventRepository $eventRepository): Response
    {
        $event = new Event();
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $event->setOwner($this->getUser());
            $eventRepository->save($event, true);

            return $this->redirectToRoute('admin_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('event/admin/new.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/event/{id}', name: 'app_event_show', methods: ['GET'])]
    public function show(Event $event,EventRepository $eventRepository): Response
    {
        $events = $eventRepository->findEventsWithSharedCategories($event,3);
        $eventsSameOwner = $eventRepository->findEventsWithSameOwner($event,3);
        return $this->render('event/show.html.twig', [
            'event' => $event,
            'moreEvents' => $events,
            'eventsSameOwner' => $eventsSameOwner,
        ]);
    }

    #[Route('/admin/event/{id}', name: 'admin_event_show', methods: ['GET'])]
    public function adminShow(Event $event,EventRepository $eventRepository): Response
    {
        return $this->render('event/admin/show.html.twig', [
            'event' => $event,
        ]);
    }

    #[Route('/admin/event/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Event $event, EventRepository $eventRepository): Response
    {
        $form = $this->createForm(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->save($event, true);

            return $this->redirectToRoute('admin_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('event/admin/edit.html.twig', [
            'event' => $event,
            'form' => $form,
        ]);
    }

    #[Route('/admin/event/{id}', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EventRepository $eventRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId(), $request->request->get('_token'))) {
            $eventRepository->remove($event, true);
        }

        return $this->redirectToRoute('admin_events_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/event/{categoryName}/category', name: 'similar_events_category', methods: ['GET'])]
    public function similarEventsByCategory($categoryName,Request $request, EventRepository $eventRepository,CategoryRepository $categoryRepository): Response
    {
        $category=$categoryRepository->findOneBy(["name"=>$categoryName]);
        $events=$eventRepository->findEventsWithCategory($category->getId(), 20);
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'categoryName' => $category,
        ]);
    }
    #[Route('/event/{tagName}/tag', name: 'similar_events_tag', methods: ['GET'])]
    function similarEventsByTag($tagName,Request $request, EventRepository $eventRepository,TagRepository $tagRepository): Response
     {
         $tag=$tagRepository->findOneBy(["name"=>$tagName]);
         $events = $eventRepository->findEventsWithTag($tagName, 20);
         return $this->render('event/index.html.twig', [
             'events' => $events,
             'tagName' => $tagName,
         ]);
     }
    #[Route('/event/{ownerName}/list', name: 'events_same_owner', methods: ['GET'])]
    function eventsWithSameOwner($ownerName,Request $request, EventRepository $eventRepository,UserRepository $userRepository): Response
    {
        $owner=$userRepository->findOneBy(['fullName'=>$ownerName]);
        $events = $eventRepository->allEventsByOwner($owner, 20);
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'owner' => $owner
        ]);
    }
    #[Route('/event/{animatorName}/animateur', name: 'events_same_animator', methods: ['GET'])]
    function eventsWithSameAnimator($animatorName, EventRepository $eventRepository,UserRepository $userRepository): Response
    {
        $animator=$userRepository->findOneBy(['fullName'=>$animatorName]);
        $events = $eventRepository->findEventsWithAnimator($animator, 20);
        return $this->render('event/index.html.twig', [
            'events' => $events,
            'animator' => $animator
        ]);
    }

}
