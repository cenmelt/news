<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\MovieWatched;

class AccountController extends AbstractController
{
    #[Route('/account', name: 'account_page')]
    public function account(EntityManagerInterface $em): Response
    {
        // Get the currently logged-in user
        $user = $this->getUser();

        // Redirect to login page if user is not logged in
        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }

        // Fetch movies watched by the logged-in user, sorted by watchedAt
        $movies = $em->getRepository(MovieWatched::class)->findBy(
            ['user' => $user],            // Filter by the current user
            ['watchedAt' => 'ASC']        // Sort by watched date (earliest first)
        );

        // Render the account page with user and movies data
        return $this->render('account.html.twig', [
            'user' => $user,              // Pass the user object to the template
            'movies' => $movies,          // Pass the movies watched by the user
        ]);
    }
}
