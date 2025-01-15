<?php 

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbService
{
    public function __construct(private HttpClientInterface $client, private string $apiKey)
    {}

    public function searchMovies(string $query, MovieFilter $filter): array
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

        $yearFrom = $filter->getYearFrom();
        $yearTo = $filter->getYearTo();
        $genre = $filter->getGenre();
        $sortBy = $filter->getSortBy();

        if ($yearFrom === 0) {
            $yearFrom = $yearTo;
        }

        if ($genre === 0) {
            $genre = null;
        }

        if ($yearTo === 0) {
            $yearTo = $yearFrom;
        }

        if ($yearFrom && $yearTo) {
            $movies = array_filter($movies, function ($movie) use ($yearFrom, $yearTo) {
                $movieYear = substr($movie['release_date'], 0, 4);
                return $movieYear >= $yearFrom && $movieYear <= $yearTo;
            });
        }

        if ($genre !== null) {
            $movies = array_filter($movies, function ($movie) use ($genre) {
                return in_array($genre, $movie['genre_ids']);
            });
        }

        if ($sortBy === 'popularity.desc') {
            usort($movies, fn($a, $b) => $b['popularity'] <=> $a['popularity']);
        } elseif ($sortBy === 'popularity.asc') {
            usort($movies, fn($a, $b) => $a['popularity'] <=> $b['popularity']);
        } elseif ($sortBy === 'release_date.desc') {
            usort($movies, fn($a, $b) => strtotime($b['release_date']) <=> strtotime($a['release_date']));
        } elseif ($sortBy === 'release_date.asc') {
            usort($movies, fn($a, $b) => strtotime($a['release_date']) <=> strtotime($b['release_date']));
        }
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
