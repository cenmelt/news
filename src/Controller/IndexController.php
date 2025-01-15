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

    private function getPaginationInfo(array $movies): array
    {
        return [
            'totalPages' => $movies['total_pages'] ?? 0,
            'currentPage' => $movies['page'] ?? 1,
        ];
    }

    #[Route('/', name: 'app_index')]
    public function index(Request $request): Response
    {
        $page = $request->query->get('page');

        if($page == null)
        {
            $page = 1;
        }
    
        $mostViewedMovies = $this->tmdbService->trendingMovies($page);

        $pagination = $this->getPaginationInfo($mostViewedMovies);

        if($pagination['totalPages'] > 10)
        {
            $pagination['totalPages'] = 10;
        }

        return $this->render('index/index.html.twig', [
            'mostViewedMovies' => $mostViewedMovies['results'] ?? [],
            'totalPages' => $pagination['totalPages'],
            'currentPage' => $pagination['currentPage'],
        ]);
    }

    #[Route('/film/{id}', name: 'movie_details')]
    public function detail(Request $request, int $id, MovieWatchedRepository $movieWatchedRepository): Response
    {
        $page = $request->query->get('page');

        if($page == null)
        {
            $page = 1;
        }
        $user = $this->getUser();

        $movieAlreadyWatched = $movieWatchedRepository->findOneBy([
            'user' => $user,
            'movieId' => $id,
        ]);
        
        $movie = $this->tmdbService->getMovieDetails($id);

        return $this->render('movie/film.html.twig', [
            'movie' => $movie,
            'currentPage' => $page,
            'movieAlreadyWatched' => $movieAlreadyWatched !== null,
        ]);
    }
}
