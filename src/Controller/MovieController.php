<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\MovieWatched;
use App\Service\TmdbService;

class MovieController extends AbstractController
{
    private $tmdbService;
    private $em;

    public function __construct(TmdbService $tmdbService, EntityManagerInterface $em)
    {
        $this->tmdbService = $tmdbService;
        $this->em = $em;
    }

    #[Route('/search', name: 'movie_search')]
    public function search(Request $request): Response
    {
        $user = $this->getUser();
        if($user == null)
        {
            return $this->redirectToRoute('app_login');
        }
        $query = $request->query->get('query', '');
        $movies = $query ? $this->tmdbService->searchMovies($query) : [];

        return $this->render('movie/search.html.twig', [
            'movies' => $movies['results'] ?? [],
            'query' => $query,
        ]);
    }

    #[Route('/watched/add/{id}', name: 'movie_watched_add')]
    public function addToWatchedList(int $id, Request $request): Response
    {
        $user = $this->getUser();

        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }

        $title = $request->query->get('title');
        $posterPath = $request->query->get('poster_path');

        if (!$id) {
            throw new \InvalidArgumentException("Movie ID cannot be null or invalid.");
        }

        $movie = new MovieWatched();
        $movie->setMovieId($id);
        $movie->setTitle($title);
        $movie->setPosterPath($posterPath);

        // Set the current user's ID
        $movie->setUserId($user->getId());

        $this->em->persist($movie);
        $this->em->flush();

        return $this->redirectToRoute('movie_search');
    }


    #[Route('/watched', name: 'movie_watched_list')]
    public function watchedList(): Response
    {
        $user = $this->getUser();
    
        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }
    
        $movies = $this->em->getRepository(MovieWatched::class)->findBy(['userId' => $user->getId()]);
    
        return $this->render('movie/watched.html.twig', [
            'movies' => $movies,
        ]);
    }
        
}
