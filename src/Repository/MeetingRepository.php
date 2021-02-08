<?php

namespace App\Repository;

use App\Entity\Meeting;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Meeting|null find($id, $lockMode = null, $lockVersion = null)
 * @method Meeting|null findOneBy(array $criteria, array $orderBy = null)
 * @method Meeting[]    findAll()
 * @method Meeting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeetingRepository extends ServiceEntityRepository
{
    public const MEETING_PER_PAGE = 20;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meeting::class);
    }

    /**
     * Возвращает список встреч постранично
     *
     * @param int $offset
     * @return Paginator
     */
    public function getMeetingsPaginator(int $offset)
    {
        $q = $this->createQueryBuilder('m')
            ->select('m, u')
            ->join('m.user', 'u')
            ->orderBy('m.beginsAt', 'ASC')
            ->addOrderBy('m.id', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults(self::MEETING_PER_PAGE)
            ->getQuery()
            ;

        return new Paginator($q);
    }
}
