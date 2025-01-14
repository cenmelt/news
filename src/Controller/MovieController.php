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
        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }
    
        $query = $request->query->get('query');
        $sortBy = $request->query->get('sort_by', 'popularity.desc'); 
        $year = $request->query->get('year'); 
        $year2 = $request->query->get('year2'); 

        if(is_string($year))
        {
            $year = null;
        }

        if(is_string($year2))
        {
            $year2 = null;
        }


        $movies = $query ? $this->tmdbService->searchMovies($query, $sortBy, $year, $year2) : [];
    
        return $this->render('movie/search.html.twig', [
            'movies' => $movies ?? [],
            'query' => $query,
            'sort_by' => $sortBy, 
            'year' => $year,
            'year2' => $year2,
        ]);
    }

    #[Route('/watched/add/{id}', name: 'movie_watched_add')]
    public function addToWatchedList(int $id, Request $request): Response
    {
        $user = $this->getUser();
        if($user == null)
        {
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
    
        $this->em->persist($movie);
        $this->em->flush();
    
        return $this->redirectToRoute('movie_search');
    }

    #[Route('/watched', name: 'movie_watched_list')]
    public function watchedList(): Response
    {
        $user = $this->getUser();
        if($user == null)
        {
            return $this->redirectToRoute('app_login');
        }
        $movies = $this->em->getRepository(MovieWatched::class)->findAll();

        return $this->render('movie/watched.html.twig', [
            'movies' => $movies,
        ]);
    }
}
