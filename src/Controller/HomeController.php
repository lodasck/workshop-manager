<?php

namespace App\Controller;

use App\Form\ContactFormType;
use App\Service\ContactService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    private ContactService $contactService;

    public function __construct(ContactService $contactService)
    {
        $this->contactService = $contactService;
    }

    #[Route('/', name: 'app_home')]
    public function sendEmail(Request $request): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $this->contactService->sendEmail(
                $data['Email'], 
                $data['Objet'], 
                $data['Contenu']
            );
            
            $this->addFlash('success', 'Email envoyé avec succès !');
            return $this->redirectToRoute('app_home');

        }

        return $this->render('home/home.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
