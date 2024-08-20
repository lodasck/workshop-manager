<?php

namespace App\Controller;

use App\Form\ContactFormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function sendEmail(Request $request, MailerInterface $mailer): Response
    {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (new Email())
                ->from($data['Email'])
                ->to('contact@workshop-manager.com')
                ->subject($data['Objet'])
                ->text($data['Contenu']);

            $mailer->send($email);

            $this->addFlash('success', 'Email envoyé avec succès!');

            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/home.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
