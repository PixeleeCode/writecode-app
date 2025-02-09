<?php

namespace App\Repository;

use App\Entity\Course;
use App\Entity\Technologie;
use App\Entity\Training;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Course|null find($id, $lockMode = null, $lockVersion = null)
 * @method Course|null findOneBy(array $criteria, array $orderBy = null)
 * @method Course[]    findAll()
 * @method Course[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CourseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Course::class);
    }

    public function queryAll(): Query
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.draft = :draft')
            ->setParameter('draft', false)
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
        ;
    }

    public function queryByTechnology(Technologie $technologie): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.technology', 't')
            ->andWhere('c.draft = :draft')
            ->andWhere('t = :technologie')
            ->setParameters([
                'draft' => false,
                'technologie' => $technologie,
            ])
            ->orderBy('c.created_at', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function queryByTraining(Training $training): array
    {
        return $this->createQueryBuilder('c')
            ->innerJoin('c.chapters', 'ch')
            ->andWhere('c.draft = :draft')
            ->andWhere('ch.training = :training')
            ->setParameters([
                'draft' => false,
                'training' => $training,
            ])
            ->orderBy('ch.position', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function filterByStatusOrTechnology(?bool $status = null, ?Technologie $technologie = null): Query
    {
        $query = $this->createQueryBuilder('c')
            ->orderBy('c.created_at', 'DESC')
        ;

        if (null !== $status) {
            $query->andWhere('c.draft = :draft')
                ->setParameter('draft', $status);
        }

        if (null !== $technologie) {
            $query->innerJoin('c.technology', 't')
                ->andWhere('t = :technologie')
                ->setParameter('technologie', $technologie);
        }

        return $query->getQuery();
    }
}
