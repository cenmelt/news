<?php

namespace App\Service;


class MovieProcessorService 
{
    public static function filterByYear(array $movies, ?int $yearFrom, ?int $yearTo): array
    {
        if (!$yearFrom || !$yearTo) {
            return $movies;
        }

        return array_filter($movies, function ($movie) use ($yearFrom, $yearTo) {
            $movieYear = substr($movie['release_date'], 0, 4);
            return $movieYear >= $yearFrom && $movieYear <= $yearTo;
        });

    }

    public static function filterByGenre(array $movies, ?int $genre): array
    {
        if ($genre === null) {
            return $movies;
        }

        return array_filter($movies, function ($movie) use ($genre) {
            return in_array($genre, $movie['genre_ids']);
        });
    }

    public static function sortMovies(array &$movies, string $sortBy): array
    {
        if ($sortBy === 'popularity.desc') {
            usort($movies, fn($a, $b) => $b['popularity'] <=> $a['popularity']);
            return $movies;

        } elseif ($sortBy === 'popularity.asc') {
            usort($movies, fn($a, $b) => $a['popularity'] <=> $b['popularity']);
            return $movies;

        } elseif ($sortBy === 'release_date.desc') {
            usort($movies, fn($a, $b) => strtotime($b['release_date']) <=> strtotime($a['release_date']));
            return $movies;

        } elseif ($sortBy === 'release_date.asc') {
            usort($movies, fn($a, $b) => strtotime($a['release_date']) <=> strtotime($b['release_date']));
            return $movies;
        }
    }
}