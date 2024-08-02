<?php

namespace App\Controller;

use App\Form\ContactFormType;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function sendEmail(Request $request, EmailService $emailService): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        return $this->render('home/home.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
