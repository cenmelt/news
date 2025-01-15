<?php

namespace App\Repository;

use App\Entity\MovieWatched;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MovieWatched>
 */
class MovieWatchedRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MovieWatched::class);
    }


    public function findMovieIdsByUser($user): array
    {
        return $this->createQueryBuilder('mw')
            ->select('mw.movieId')
            ->where('mw.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleColumnResult();
    }
}
