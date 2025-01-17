<?php

namespace App\Service;

class MovieProcessorService
{
    /**
     * Filter movies by release year range.
     */
    public function filterByYear(array $movies, ?int $yearFrom, ?int $yearTo): array
    {
        if (!$yearFrom || !$yearTo) {
            return $movies;
        }

        return array_filter($movies, function ($movie) use ($yearFrom, $yearTo) {
            if (empty($movie['release_date']) || empty($movie['title'])) {
                return false;
            }

            $movieYear = (int) substr($movie['release_date'], 0, 4);
            return $movieYear >= $yearFrom && $movieYear <= $yearTo;
        });
    }

    /**
     * Filter movies by genre.
     */
    public function filterByGenre(array $movies, ?int $genre): array
    {
        if ($genre === null) {
            return $movies;
        }

        return array_filter($movies, function ($movie) use ($genre) {
            if (empty($movie['title']) || empty($movie['genre_ids'])) {
                return false;
            }

            return in_array($genre, $movie['genre_ids']);
        });
    }

    /**
     * Sort movies by the specified criteria.
     */
    public function sortMovies(array &$movies, string $sortBy): array
    {
        $movies = array_filter($movies, function ($movie) {
            return !empty($movie['release_date']) &&
                   !empty($movie['title']) &&
                   !empty($movie['popularity']) &&
                   !empty($movie['poster_path']);
        });

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
}
