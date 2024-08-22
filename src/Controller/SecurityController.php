<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\JwtService;
use App\Service\EmailService;
use App\Form\ResetPasswordFormType;
use App\Form\RegistrationFormType;
use App\Repository\UserRepository;
use App\Security\UserAuthenticator;
use App\Form\ForgotPasswordFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername, 
            'error' => $error]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
    
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, 
    Security $security, EntityManagerInterface $entityManager, JwtService $jwt, EmailService $mail): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();

            # Token
            $header = ['alg' => 'HS256','type' => 'JWT'];
            $payload = ['user_id' => $user->getId()];

            $token = $jwt->createToken($header, $payload, $this->getParameter('token_secret'));

            $mail->send(
                'contact@workshop-manager.com', // $from
                $user->getEmail(), // $to
                'Activation de votre compte sur WORSKSHOP-MANAGER', // $subject
                'email_verify', // $template
                compact('user', 'token') // $context
            );

            return $security->login($user, UserAuthenticator::class, 'main');
        }

        return $this->render('security/registration.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/verify{token}', name: 'app_verify')]
    public function verifUser($token, JwtService $jwt, UserRepository $userRepository, EntityManagerInterface $em): Response
    {
        if($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('token_secret')))
        {
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']);

            if($user && !$user->isVerified())
            {
                $user->setVerified(true);
                $em->flush();

                $this->addFlash('success', 'Votre compte est bien activé');
                return $this->redirectToRoute('app_dashboard');
            }
        }
        $this->addFlash('danger', 'Le token est invalide ou à expirer');
        return $this->redirectToRoute('app_register');
    }

    #[Route('/mot-de-passe-oublier', name: 'app_forgot')]
    public function forgotPassword(Request $request, EmailService $mail, UserRepository $userRepository, JwtService $jwt): Response
    {
        $form = $this->createForm(ForgotPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->getData()['email'];
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                $header = ['alg' => 'HS256', 'type' => 'JWT'];
                $payload = ['user_id' => $user->getId()];
                $token = $jwt->createToken($header, $payload, $this->getParameter('token_secret'));

                $mail->send(
                    'contact@workshop-manager.com', // $from
                    $user->getEmail(), // $to
                    'WORKSHOP-MANAGER : Mot de passe oublié', // $subject
                    'email_forgot_password', // $template
                    compact('user', 'token') // $context
                );
            }

            $this->addFlash('success', 'Le mail a été envoyé à l\'adresse indiquée');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/forgot_password.html.twig', [
            'forgotPasswordForm' => $form->createView(),
        ]);
    }

    #[Route('/reinitialisation-mot-de-passe/{token}', name: 'app_verify_password')]
    public function resetPassword($token, Request $request, JwtService $jwt, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        if ($jwt->isValid($token) && !$jwt->isExpired($token) && $jwt->check($token, $this->getParameter('token_secret'))) {
            $payload = $jwt->getPayload($token);
            $user = $userRepository->find($payload['user_id']);

            if ($user) {
                $form = $this->createForm(ResetPasswordFormType::class);

                $form->handleRequest($request);

                if ($form->isSubmitted() && $form->isValid()) {
                    $data = $form->getData();
                    if ($data['newPassword'] === $data['confirmPassword']) {
                        $user->setPassword($passwordHasher->hashPassword($user, $data['newPassword']));
                        $entityManager->flush();

                        $this->addFlash('success', 'Votre mot de passe a été mis à jour avec succès.');
                        return $this->redirectToRoute('app_login');
                    } else {
                        $this->addFlash('danger', 'Les mots de passe ne correspondent pas.');
                    }
                }

                return $this->render('security/reset_password.html.twig', [
                    'resetPasswordForm' => $form->createView(),
                    'token' => $token,
                ]);
            }
        }

        $this->addFlash('danger', 'Le lien de réinitialisation est invalide ou a expiré.');
        return $this->redirectToRoute('app_login');
    }
}
