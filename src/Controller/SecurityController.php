<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use App\Form\PassForMailFormType;
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
public function mailForPass(EntityManagerInterface $entityManager, UserRepository $userRepository, MailerInterface $mailer, Request $request): Response
{
    $form = $this->createForm(PassForMailFormType::class);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $mail = $form->get('email')->getData();
        $user = $userRepository->findOneBy(['email' => $mail]);

        if ($user !== null) {
            // Générer un hash unique et l'enregistrer dans l'utilisateur
            $resetToken = bin2hex(random_bytes(16)); // Génère un token de 32 caractères hexadécimaux
            $user->setResetToken($resetToken); // Ajoutez un champ `resetToken` à votre entité User
            $entityManager->persist($user);
            $entityManager->flush();

            // Construire l'e-mail
            $resetLink = $this->generateUrl('app_pass_reset', ['id' => $user->getId(), 'token' => $resetToken], UrlGeneratorInterface::ABSOLUTE_URL);

            $email = (new Email())
                ->from('clement.collet123@orange.fr') // Adresse fixe
                ->to($user->getEmail())
                ->subject('Réinitialisation de votre mot de passe')
                ->html("
                    <p>Bonjour,</p>
                    <p>Vous avez demandé une réinitialisation de votre mot de passe. Cliquez sur le lien ci-dessous pour le réinitialiser :</p>
                    <p><a href='{$resetLink}'>Réinitialiser le mot de passe</a></p>
                    <p>Si vous n'avez pas fait cette demande, veuillez ignorer cet e-mail.</p>
                ");

            $mailer->send($email);

            $this->addFlash('success', "Un e-mail vous a été envoyé avec les instructions pour réinitialiser votre mot de passe.");
            return $this->redirectToRoute('app_login');
        } else {
            $this->addFlash('error', "Aucun compte n'existe avec cette adresse e-mail.");
        }
    }

    return $this->render('security/mailForPass.html.twig', [
        'PassForMailForm' => $form,
    ]);
}

#[Route(path: '/passForget/{id}/{token}', name: 'app_pass_reset')]
public function passForget(
    EntityManagerInterface $entityManager,
    UserRepository $userRepository,
    UserPasswordHasherInterface $passwordHasher,
    Request $request,
    int $id,
    string $token
): Response {
    $user = $userRepository->find($id);

    if (!$user || $user->getResetToken() !== $token) {
        $this->addFlash('error', 'Lien invalide ou expiré.');
        return $this->redirectToRoute('app_login');
    }

    $form = $this->createForm(ResetPasswordFormType::class);


    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $newPassword = $form->get('password')->getData();
        $hashedPassword = $passwordHasher->hashPassword($user, $newPassword);

        $user->setPassword($hashedPassword);
        $user->setResetToken(null); // Supprime le token après réinitialisation
        $entityManager->persist($user);
        $entityManager->flush();

        $this->addFlash('success', 'Votre mot de passe a été réinitialisé avec succès.');
        return $this->redirectToRoute('app_login');
    }

    return $this->render('security/resetPassword.html.twig', [
        'resetForm' => $form->createView(),
    ]);
}
}
