<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\ForgotPasswordFormType;
use App\Form\ResetPasswordFormType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mime\Email;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Uuid;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use App\Repository\UserRepository;




class SecurityController extends AbstractController
{
    #[Route(path: '/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $user = $this->getUser();
        if($user != null)
        {
            return $this->redirectToRoute('app_index');
        }
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('security/sign_in.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/resetPassword', name: 'app_mailPass')]
    public function ForgotPassword(EntityManagerInterface $entityManager, UserRepository $userRepository, MailerInterface $mailer, Request $request): Response
    {
        $form = $this->createForm(forgotPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mail = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $mail]);

            if ($user !== null) {
                $resetToken = bin2hex(random_bytes(16)); 
                $user->setResetToken($resetToken); 
                $entityManager->persist($user);
                $entityManager->flush();

                $resetLink = $this->generateUrl('app_pass_reset', ['id' => $user->getId(), 'token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

                $email = (new Email())
                    ->from('clement.collet123@orange.fr') // Email sender
                    ->to($user->getEmail())
                    ->subject('Réinitialisation de votre mot de passe')
                    ->html("
                        <p>Hello,</p>
                        <p>You have requested a password reset. Click the link below to reset it :</p>
                        <p><a href='{$resetLink}'>RESET PASSWORD/a></p>
                        <p>If you have not made this request, please ignore this email.</p>
                    ");

                $mailer->send($email);

                $this->addFlash('success', "An email has been sent to you with instructions to reset your password");
                return $this->redirectToRoute('app_login');
            } else {
                $this->addFlash('error', "No account exists with this email address.");
            }
        }

        return $this->render('security/forgot_password.html.twig', [
            'ForgotPasswordForm' => $form,
        ]);
    }

    #[Route(path: '/resetPassword/{id}/{token}', name: 'app_pass_reset')]
    public function resetPassword(EntityManagerInterface $entityManager,UserRepository $userRepository,UserPasswordHasherInterface $passwordHasher,Request $request,int $id,string $token): Response {
        $user = $userRepository->find($id);

        if (!$user || $user->getResetToken() !== $token) {
            $this->addFlash('error', 'Invalid or expired link');
            return $this->redirectToRoute('app_login');
        }

        $form = $this->createForm(ResetPasswordFormType::class);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newPassword = $form->get('password')->getData();
            $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);

            $user->setPassword($hashedPassword);
            $user->setResetToken(null); // Remove password after authentication 
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Your password has been successfully reset');
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', [
            'resetForm' => $form->createView(),
        ]);
    }
}
