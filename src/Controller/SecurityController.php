<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\PassForMailFormType;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\HttpFoundation\Request;
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

        return $this->render('security/login.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route(path: '/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    #[Route(path: '/passForget', name: 'app_mailPass')]
    public function mailForPass(UserRepository $userRepository,MailerInterface $mailer, Request $request, AuthenticationUtils $authenticationUtils): Response
    {
        $form = $this->createForm(PassForMailFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var string $plainPassword */
            $mail = $form->get('email')->getData();

            $user = $userRepository->findOneBy(['email' => $mail]);
            if($user != null)
            {
                $email = (new Email())
                    ->from('clement.collet123@orange.fr') // tjrs garder ce mail
                    ->to($user->getEmail())
                    ->subject('Test Email')
                    ->text('This is a test email.')
                    ->html('<p>This is a test email.</p>');
        
        
                $mailer->send($email);
                $this->addFlash('success', "Un mail vous a été envoyé .");

            }
            else
            {
                $this->addFlash('error', "Aucun compte n'as été créée avec cette adresse mail.");
                return $this->redirectToRoute('app_register');
            }

        }

        return $this->render('security/mailForPass.html.twig', [
            'PassForMailForm' => $form,
        ]);
    }

    #[Route(path:'/test-kadem', name: "kadem")]
    public function testKadem(MailerInterface $mailer): Response
    {
        $email = (new Email())
        ->from('clement.collet123@orange.fr')
        ->to('kademkamilg@gmail.com')
        ->subject('Test Email')
        ->text('This is a test email.')
        ->html('<p>This is a test email.</p>');

    $mailer->send($email);
    $this->addFlash('success', "Un mail vous a été envoyé .");

    return $this->redirectToRoute('app_mailPass');
    }
}
