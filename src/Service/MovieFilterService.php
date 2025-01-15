<?php

namespace App\Service;

class MovieFilterService
{
    private ?int $yearFrom;
    private ?int $yearTo;
    private ?int $genre;
    private string $sortBy;

    public function __construct(
        ?int $yearFrom = null,
        ?int $yearTo = null,
        ?int $genre = null,
        string $sortBy = 'popularity.desc'
    ) {
        $this->yearFrom = $yearFrom;
        $this->yearTo = $yearTo;
        $this->genre = $genre;
        $this->sortBy = $sortBy;
        $this->syncYears();
    }

    public function getYearFrom(): ?int
    {
        return $this->yearFrom;
    }

    public function getYearTo(): ?int
    {
        return $this->yearTo;
    }

    public function getGenre(): ?int
    {
        return $this->genre;
    }

    public function getSortBy(): string
    {
        return $this->sortBy;
    }

    public function setYearFrom(?int $yearFrom): void
    {
        $this->yearFrom = $yearFrom === 0 ? null : $yearFrom;
        $this->syncYears();
    }

    public function setYearTo(?int $yearTo): void
    {
        $this->yearTo = $yearTo === 0 ? null : $yearTo;
        $this->syncYears();
    }

    public function setGenre(?int $genre): void
    {
        $this->genre = $genre === 0 ? null : $genre;
    }

    public function setSortBy(string $sortBy): void
    {
        $this->sortBy = $sortBy;
    }

    /**
     * Synchronize yearFrom and yearTo values to ensure consistency.
     */
    private function syncYears(): void
    {
        if ($this->yearFrom !== null && $this->yearTo === null) {
            $this->yearTo = $this->yearFrom;
        } elseif ($this->yearTo !== null && $this->yearFrom === null) {
            $this->yearFrom = $this->yearTo;
        }
    }
}
