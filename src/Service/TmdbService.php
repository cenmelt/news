<?php 

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class TmdbService
{
    public function __construct(private HttpClientInterface $client, private string $apiKey)
    {}

    public function searchMovies(string $query, string $sortBy= 'popularity.desc', ?int $year = null, ?int $year2 = null, ?int $genre = null): array
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

        if($year == 0)
        {
            $year = $year2;
        }

        if($genre == 0)
        {
            $genre = null;
        }

        if($year2 == 0)
        {
            $year2 = $year;
        }

        if ($year != 0 && $year2 != 0) {
            $movies = array_filter($movies, function ($movie) use ($year, $year2) {
                $movieYear = substr($movie['release_date'], 0, 4); 
                return $movieYear >= $year && $movieYear <= $year2;
            });
        }

        if ($genre !== null) {
            $movies = array_filter($movies, function ($movie) use ($genre) {
                return in_array($genre, $movie['genre_ids']);
            });
        }
        
        if ($sortBy === 'popularity.desc') {
            usort($movies, function ($a, $b) {
                return $b['popularity'] <=> $a['popularity'];
            });
        } elseif ($sortBy === 'popularity.asc') {
            usort($movies, function ($a, $b) {
                return $a['popularity'] <=> $b['popularity'];
            });
        } elseif ($sortBy === 'release_date.desc') {
            usort($movies, function ($a, $b) {
                return strtotime($b['release_date']) <=> strtotime($a['release_date']);
            });
        } elseif ($sortBy === 'release_date.asc') {
            usort($movies, function ($a, $b) {
                return strtotime($a['release_date']) <=> strtotime($b['release_date']);
            });
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
