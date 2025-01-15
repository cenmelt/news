<?php 

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\MovieProcessorService;



class TmdbService
{
    public function __construct(private HttpClientInterface $client, private string $apiKey)
    {}

    public function searchMovies(string $query, MovieFilterService $filter): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.themoviedb.org/3/search/movie',
            [
                'query' => [
                    'api_key' => $this->apiKey,
                    'query' => $query,
                ],
            ]
        );

        $movies = $response->toArray()['results'] ?? [];

        $movies = MovieProcessorService::filterByYear($movies, $filter->getYearFrom(), $filter->getYearTo());
        
        $movies = MovieProcessorService::filterByGenre($movies, $filter->getGenre());

        MovieProcessorService::sortMovies($movies, $filter->getSortBy());

        return $movies;
    }

    public function trendingMovies(): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.themoviedb.org/3/trending/movie/day',
            [
                'query' => [
                    'api_key' => $this->apiKey,
                ],
            ]
        );

        return $response->toArray();
    }

    public function getGenres(): array
    {
        $url = "https://api.themoviedb.org/3/genre/movie/list?api_key=".$this->apiKey."";
        
        // Effectuer l'appel API
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        
        return $data['genres'] ?? [];
    }

    public function getMovieDetails(int $id): array
    {
        $response = $this->client->request(
            'GET',
            'https://api.themoviedb.org/3/movie/'.$id,
            [
                'query' => [
                    'api_key' => $this->apiKey,
                ],
            ]
        );
        
        return $response->toArray();
    }

}
