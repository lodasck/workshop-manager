<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JWTService;
use App\Service\SendEmailService;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, 
    Security $security, EntityManagerInterface $entityManager, JWTService $jwt, SendEmailService $mail): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            // do anything else you need here, like send an email

            ## generate token
            # Header
            $header = [
                'typ' => 'JWT',
                'alg' => 'HS256'
            ];

            # Payload
            $payload = [
                'user_id' => $user->getId()
            ];

            # generate token
            $token = $jwt->generate($header, $payload, $this->getParameter('jwtsecret'));

            # Send email
            $mail->send(
                'no-reply@ws-manager.test',
                $user->getEmail(),
                "Activation de votre compte sur l\'application' WORKSHOP-MANAGER",
                "confirm",
                compact('user', 'token')
            );

            return $security->login($user, UserAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verif/{token}', name: 'app_verify')]
    public function verifUser($token, JWTService $jwt, UserRepository $userRepository, 
    EntityManagerInterface $em): Response
    {
        # check if token is valid
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check
        ($token, $this->getParameter('jwtsecret')))
        {
            $payload = $jwt->getPayload($token);

            $user = $userRepository->find($payload['user_id']);

            if($user && !$user->isVerified())
            {
                $user->setIsVerified(true);
                $em->flush();

                $this->addFlash('success', 'Utilisateur activé');
                return $this->redirectToRoute('app_login');
            }
        }
        $this->addFlash('danger', 'Le token est invalide ou a expiré');
        return $this->redirectToRoute('app_login');
    }
}
