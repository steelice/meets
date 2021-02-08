<?php


namespace App\Service;


use App\Entity\Meeting;
use App\Entity\MeetingComment;
use App\Entity\MeetingVisitor;
use App\Entity\User;
use App\Message\SendEmailMessage;
use App\Repository\MeetingCommentRepository;
use App\Repository\MeetingRepository;
use App\Repository\MeetingVisitorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Environment;

class MeetingService
{
    public const PHOTO_HEIGHT = 300;
    public const PHOTO_WIDTH = 300;
    public const PHOTO_THUMB_HEIGHT = 800;
    public const PHOTO_THUMB_WIDTH = 600;
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private EntityManagerInterface $em;
    /**
     * @var \App\Repository\MeetingRepository
     */
    private MeetingRepository $meetingRepository;
    /**
     * @var \App\Repository\MeetingCommentRepository
     */
    private MeetingCommentRepository $commentRepository;
    /**
     * @var \App\Repository\MeetingVisitorRepository
     */
    private MeetingVisitorRepository $visitorRepository;
    /**
     * @var \Symfony\Component\Messenger\MessageBusInterface
     */
    private MessageBusInterface $bus;
    /**
     * @var \Symfony\Component\Routing\Generator\UrlGeneratorInterface
     */
    private UrlGeneratorInterface $router;
    /**
     * @var \App\Service\FileStorageService
     */
    private FileStorageService $storageService;


    public function __construct(EntityManagerInterface $em, MeetingRepository $meetingRepository, MeetingCommentRepository $commentRepository,
                                MeetingVisitorRepository $visitorRepository, MessageBusInterface $bus, UrlGeneratorInterface $router, FileStorageService $storageService)
    {
        $this->em = $em;
        $this->meetingRepository = $meetingRepository;
        $this->commentRepository = $commentRepository;
        $this->visitorRepository = $visitorRepository;
        $this->bus = $bus;
        $this->router = $router;
        $this->storageService = $storageService;
    }

    /**
     * Создает встречу, загружает файлы, ресайзит их до необходмого размера, и так далее
     * @param $meeting
     */
    public function create(Meeting $meeting)
    {

        if ($meeting->getMainPhotoFile()) {
            $file = $this->storageService->saveImage($meeting->getMainPhotoFile(), self::PHOTO_THUMB_WIDTH, self::PHOTO_THUMB_HEIGHT);
            $meeting->setMainPhoto(basename($file));
        }

        foreach ($meeting->getGalleryPhotoFiles() as $file) {
            $file = basename($this->storageService->saveImage($file, self::PHOTO_THUMB_WIDTH, self::PHOTO_THUMB_HEIGHT));
            $meeting->addGalleryPhoto($file);
        }

        $this->em->persist($meeting);
        $this->em->flush();


    }

    /**
     * Добавляет комментарий и отправляет об этом уведомление
     *
     * @param \App\Entity\MeetingComment $comment
     * @param \App\Entity\User $user
     */
    public function addComment(MeetingComment $comment, User $user)
    {
        $comment->setUser($user);

        // для комментариев под собственной встречей апрува не нужно
        $needApprove = $comment->getMeeting()->getUser()->getId() !== $user->getId();

        $comment->setIsApproved(!$needApprove);

        $this->em->persist($comment);
        $this->em->flush();

        if ($needApprove) {
            $this->bus->dispatch(new SendEmailMessage(
                $comment->getMeeting()->getUser()->getEmail(),
                'Новый комментарий к вашей встрече',
                'mail/new-comment-approve.html.twig',
                [
                    'text' => $comment->getText(),
                    'author_email' => $comment->getUser()->getEmail(),
                    'meeting_url' => $this->router->generate('meeting_view', ['url' => $comment->getMeeting()->getUrl()], UrlGeneratorInterface::ABSOLUTE_URL),
                    'approve_url' => $this->router->generate('comment_approve', ['id' => $comment->getId(), 'hash' => $comment->getApproveHash()], UrlGeneratorInterface::ABSOLUTE_URL),
                    'deny_url' => $this->router->generate('comment_deny', ['id' => $comment->getId(), 'hash' => $comment->getApproveHash()], UrlGeneratorInterface::ABSOLUTE_URL),
                ]
            ));
        }
    }

    /**
     * Возвращает список комментариев для встречи с учетом их видимости для заданного пользователя
     *
     * @param \App\Entity\Meeting $meeting
     * @param \App\Entity\User|null $user
     * @return \App\Entity\MeetingComment[]
     */
    public function getComments(Meeting $meeting, ?User $user)
    {
        return $this->commentRepository->getCommentsForMeetingAndUser($meeting, $user);
    }

    /**
     * Подтверждает комментарий
     *
     * @param \App\Entity\MeetingComment $comment
     */
    public function approveComment(MeetingComment $comment)
    {
        if ($comment->getIsApproved()) {
            return;
        }
        $comment->setIsApproved(true);
        $this->em->flush();

        $this->em->createQuery('UPDATE \App\Entity\Meeting m SET m.totalComments = m.totalComments + 1 WHERE m = :meeting')
            ->setParameter('meeting', $comment->getMeeting())->execute();

        // @todo: Возможно стоит уведомить владельца комментария о том, что его коммент подтвердили
    }

    /**
     * Помечает, что пользователь придёт на встречу
     *
     * @param \App\Entity\Meeting $meeting
     * @param \App\Entity\User $user
     */
    public function userGoing(Meeting $meeting, User $user)
    {
        $ug = new MeetingVisitor($meeting, $user);

        $this->em->persist($ug);
        $this->em->flush();

        $this->em->createQuery('UPDATE \App\Entity\Meeting m SET m.usersGoing = m.usersGoing + 1 WHERE m = :meeting')
            ->setParameter('meeting', $meeting)->execute();

        // @todo: Возможно стоит уведомить владельца встрече о прихожанине

    }

    /**
     * Удаляет комментарий
     *
     * @param \App\Entity\MeetingComment $comment
     */
    public function denyComment(MeetingComment $comment)
    {
        $comment->getMeeting()->removeComment($comment);
        if ($comment->getIsApproved()) {
            $this->em->createQuery('UPDATE \App\Entity\Meeting m SET m.totalComments = m.totalComments - 1 WHERE m = :meeting')
                ->setParameter('meeting', $comment->getMeeting())->execute();

        }
        $this->em->flush();

    }

    /**
     * Возвращает признак для одной встречи, собирается ли на неё пользователь
     *
     * @param \App\Entity\Meeting $meeting
     * @param \App\Entity\User $user
     * @return bool
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function isUserGoing(Meeting $meeting, ?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->em->createQueryBuilder()
                ->select('v')
                ->from(MeetingVisitor::class, 'v')
                ->setMaxResults(1)
                ->where('v.meeting = :meeting AND v.user = :user')
                ->setParameters([
                    'meeting' => $meeting,
                    'user' => $user,
                ])
                ->getQuery()
                ->getOneOrNullResult() !== null;
    }
}