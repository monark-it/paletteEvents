<?php

/*
 * This file is part of the PaletteEvent project.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Developed by Monarkit
 *
 */

namespace App\Controller;

use App\Entity\Category;
use App\Entity\Event;
use App\Repository\EventRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function index(EventRepository $eventRepository){
        // call findLastEvents from EventRepository
        $events = $eventRepository->findLastEvents(4);
        $categories = $this->getDoctrine()->getRepository(Category::class)->findCategories(6);

        return $this->render('home.html.twig',[
            'events' => $events,
            'categories' => $categories,
        ]);
    }

    /**
     * @Route("/admin", name="admin")
     */
    public function admin(){
        return $this->render('admin.html.twig');
    }

    /**
     * @Route("/contact", name="contact")
     */
    public function contact(){
        return $this->render('contact/form.html.twig');
    }
}
