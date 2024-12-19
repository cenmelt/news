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
    private $movieId;

    #[ORM\Column(type: 'string', length: 255)]
    private $title;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $posterPath;

  
    public function getId(): ?int
    {
        return $this->id;
    }
}

