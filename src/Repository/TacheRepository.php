<?php
namespace App\Repository;

use App\Entity\Tache;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class TacheRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tache::class);
    }

    public function findByUtilisateur(int $userId): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.utilisateur = :userId')
            ->setParameter('userId', $userId)
            ->orderBy('t.id','DESC')
            ->getQuery()
            ->getResult();
    }
}
