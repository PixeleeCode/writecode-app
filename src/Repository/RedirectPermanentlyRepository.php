<?php

namespace App\Repository;

use App\Entity\RedirectPermanently;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method RedirectPermanently|null find($id, $lockMode = null, $lockVersion = null)
 * @method RedirectPermanently|null findOneBy(array $criteria, array $orderBy = null)
 * @method RedirectPermanently[]    findAll()
 * @method RedirectPermanently[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RedirectPermanentlyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RedirectPermanently::class);
    }
}
