<?php

namespace App\Entity;

use App\Repository\MovieWatchedRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: MovieWatchedRepository::class)]
class MovieWatched
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'integer')]
    private ?int $movieId = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $posterPath = null;

    // Getter for ID
    public function getId(): ?int
    {
        return $this->id;
    }

    // Getter and Setter for movieId
    public function getMovieId(): ?int
    {
        return $this->movieId;
    }

    public function setMovieId(int $movieId): self
    {
        $this->movieId = $movieId;
        return $this;
    }

    // Getter and Setter for title
    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    // Getter and Setter for posterPath
    public function getPosterPath(): ?string
    {
        return $this->posterPath;
    }

    public function setPosterPath(?string $posterPath): self
    {
        $this->posterPath = $posterPath;
        return $this;
    }
}
