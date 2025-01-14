<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\TmdbService;
use App\Repository\MovieWatchedRepository;

class IndexController extends AbstractController
{
    private $tmdbService;
    private $em;

    public function __construct(TmdbService $tmdbService, EntityManagerInterface $em)
    {
        $this->tmdbService = $tmdbService;
        $this->em = $em;
    }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        $mostViewedMovies = $this->tmdbService->trendingMovies();

        return $this->render('index/index.html.twig', [
            'mostViewedMovies' => $mostViewedMovies['results'] ?? [],
        ]);
    }

    #[Route('/film/{id}', name: 'movie_details')]
    public function detail(Request $request, int $id, MovieWatchedRepository $movieWatchedRepository): Response
    {
        $user = $this->getUser();

        $movieAlreadyWatched = $movieWatchedRepository->findOneBy([
            'user' => $user,
            'movieId' => $id,
        ]);
        
        $movie = $this->tmdbService->getMovieDetails($id);

        return $this->render('movie/film.html.twig', [
            'movie' => $movie,
            'movieAlreadyWatched' => $movieAlreadyWatched !== null,
        ]);
    }
}
