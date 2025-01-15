<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbService
{
    public function __construct(
        private HttpClientInterface $client,
        private string $apiKey,
        private string $apiBase, // Injected from environment variable
        private MovieProcessorService $movieProcessor
    ) {}

    /**
     * Search for movies based on a query and filter criteria.
     */
    public function searchMovies(string $query, MovieFilterService $filter, int $page = 1): array
    {
        $endpoint = "{$this->apiBase}/search/movie";
        $response = $this->client->request('GET', $endpoint, [
            'query' => [
                'api_key' => $this->apiKey,
                'query' => $query,
                'page' => $page,
            ],
        ]);

        $data = $response->toArray();

        $movies = $this->movieProcessor->filterByYear($data['results'], $filter->getYearFrom(), $filter->getYearTo());
        $movies = $this->movieProcessor->filterByGenre($movies, $filter->getGenre());
        $this->movieProcessor->sortMovies($movies, $filter->getSortBy());

        return [
            'movies' => $movies,
            'total_pages' => $data['total_pages'],
            'page' => $data['page'],
        ];
    }

    /**
     * Fetch trending movies for the day.
     */
    public function trendingMovies(): array
    {
        $endpoint = "{$this->apiBase}/trending/movie/day";
        $response = $this->client->request('GET', $endpoint, [
            'query' => [
                'api_key' => $this->apiKey,
            ],
        ]);

        return $response->toArray();
    }

    /**
     * Fetch movie genres.
     */
    public function getGenres(): array
    {
        $endpoint = "{$this->apiBase}/genre/movie/list";
        $response = $this->client->request('GET', $endpoint, [
            'query' => [
                'api_key' => $this->apiKey,
            ],
        ]);

        $data = $response->toArray();
        return $data['genres'] ?? [];
    }

    /**
     * Get details for a specific movie.
     */
    public function getMovieDetails(int $id): array
    {
        $endpoint = "{$this->apiBase}/movie/{$id}";
        $response = $this->client->request('GET', $endpoint, [
            'query' => [
                'api_key' => $this->apiKey,
            ],
        ]);

        return $response->toArray();
    }
}
