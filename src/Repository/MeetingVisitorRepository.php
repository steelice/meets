<?php

namespace App\Repository;

use App\Entity\MeetingVisitor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MeetingVisitor|null find($id, $lockMode = null, $lockVersion = null)
 * @method MeetingVisitor|null findOneBy(array $criteria, array $orderBy = null)
 * @method MeetingVisitor[]    findAll()
 * @method MeetingVisitor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeetingVisitorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MeetingVisitor::class);
    }
}
