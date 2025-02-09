<?php

namespace App\Repository;

use App\Entity\Training;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Training|null find($id, $lockMode = null, $lockVersion = null)
 * @method Training|null findOneBy(array $criteria, array $orderBy = null)
 * @method Training[]    findAll()
 * @method Training[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrainingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Training::class);
    }

    /**
     * Retourne toutes les formations ainsi que le slug du chapitre 1.
     */
    public function queryAll(): Query
    {
        return $this->createQueryBuilder('t')
            ->select('t.name', 't.slug', 't.picture', 't.updated_at', 'COUNT(c.id) AS nb_courses')
            ->innerJoin('t.chapters', 'ch')
            ->innerJoin('ch.course', 'c')
            ->andWhere('c.draft = :draft')
            ->setParameter('draft', false)
            ->groupBy('t.id')
            ->orderBy('t.created_at', 'DESC')
            ->getQuery()
        ;
    }
}
