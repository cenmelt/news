<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\MovieWatched;
use App\Service\TmdbService;
use App\Repository\MovieWatchedRepository;
use App\Service\MovieFilterService;


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
    public function search(Request $request, MovieWatchedRepository $movieWatchedRepository): Response
    {
        $user = $this->getUser();
        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }
    
        $query = $request->query->get('query');
        $yearFrom = $request->query->get('yearFrom'); 
        $yearTo = $request->query->get('yearTo'); 

        $genre = $request->query->get('genre');

        $genres = $this->tmdbService->getGenres();

        if(is_string($yearFrom))
        {
            $yearFrom = intval($yearFrom);
        }

        if (is_string($genre)) {
            $genre = intval($genre);
        }

        if(is_string($yearTo))
        {
            $yearTo = intval($yearTo);
        }

        $filter = new MovieFilterService();
        $filter->SetGenre($genre);
        $filter->SetYearFrom($yearFrom);
        $filter->SetYearTo($yearTo);
        $filter->SetSortBy($request->query->get('sort_by', 'popularity.desc'));


        $movies = $query ? $this->tmdbService->searchMovies($query, $filter) : [];

        $watchedMovieIds = $movieWatchedRepository->findMovieIdsByUser($user);

        foreach ($movies as &$movie) {
            $movie['alreadyWatched'] = in_array($movie['id'], $watchedMovieIds);
        }
    
        return $this->render('movie/search.html.twig', [
            'movies' => $movies ?? [],
            'query' => $query,
            'sort_by' => $request->query->get('sort_by', 'popularity.desc'),
            'genres' => $genres,
            'yearFrom' => $yearFrom,
            'yearTo' => $yearTo,
            'selected_genre' => $genre
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

        // Set the current date
        $movie->setWatchedAt(new \DateTime());

        // Set the current user's ID or object (if using relationships)
        $movie->setUser($user);


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

    #[Route('/timeline', name: 'movie_timeline')]
    public function timeline(Request $request): Response
    {
        $user = $this->getUser();
    
        if ($user === null) {
            return $this->redirectToRoute('app_login');
        }
    
        // Retrieve query and movieId from the request
        $query = $request->query->get('query', '');
        $movieId = $request->query->get('movieId', null);
    
        // If movieId is provided, redirect with a fragment
        if ($movieId !== null) {
            $movie = $this->em->getRepository(MovieWatched::class)->findOneBy([
                'user' => $user,
                'movieId' => (int)$movieId,
            ]);
    
            if ($movie) {
                // Redirect with an anchor to the movie
                return $this->redirectToRoute('movie_timeline', [], 302, [
                    'fragment' => 'movie-' . $movie->getMovieId(),
                ]);
            }
    
            $this->addFlash('error', 'No movie found with that ID.');
        }
    
        $moviesQuery = $this->em->getRepository(MovieWatched::class)
            ->createQueryBuilder('m')
            ->where('m.user = :user')
            ->setParameter('user', $user);
    
        if (!empty($query)) {
            $moviesQuery->andWhere('m.title LIKE :query')
                        ->setParameter('query', '%' . $query . '%');
        }
    
        $movies = $moviesQuery->orderBy('m.watchedAt', 'ASC')->getQuery()->getResult();
    
        return $this->render('movie/timeline.html.twig', [
            'movies' => $movies,
            'query' => $query,
        ]);
    }


    
}
