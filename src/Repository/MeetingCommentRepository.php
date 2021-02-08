<?php

namespace App\Repository;

use App\Entity\MeetingComment;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method MeetingComment|null find($id, $lockMode = null, $lockVersion = null)
 * @method MeetingComment|null findOneBy(array $criteria, array $orderBy = null)
 * @method MeetingComment[]    findAll()
 * @method MeetingComment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class MeetingCommentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MeetingComment::class);
    }

    /**
     * Возврпащает список комментариев для для встречи
     *
     * @param \App\Entity\Meeting $meeting
     * @param \App\Entity\User|null $user
     * @return MeetingComment[]
     */
    public function getCommentsForMeetingAndUser(\App\Entity\Meeting $meeting, ?User $user)
    {
        $qb = $this->createQueryBuilder('c')
            ->select('c, u')
            ->join('c.user', 'u')
            ->where('c.meeting = :meeting')
            ->setParameter('meeting', $meeting)
            ->orderBy('c.id', 'DESC');

        if ($user && ($user->getId() === $meeting->getUser()->getId())) {
            // для владельцев встречи отдаем все комментарии для возможности их модерации

        } elseif ($user) {
            // для авторизированного пользователя выводим и его комментарии, чтобы было видно, что они ожидают модерации
            $qb->andWhere('c.isApproved = true OR c.user = :user')
                ->setParameter('user', $user);
        } else {
            // для остальных выводим только заапрувленные комментарии
            $qb->andWhere('c.isApproved = true');
        }

        return $qb->getQuery()->getResult();
    }
}
