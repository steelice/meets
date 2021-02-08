<?php

namespace App\Controller;

use App\Entity\MeetingComment;
use App\Service\MeetingService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Работа с комментариями
 *
 * @Route ("/comment")
 */
class CommentController extends AbstractController
{
    /**
     * @Route ("/approve/{id}/{hash}", name="comment_approve")
     * @param \App\Service\MeetingService $meetingService
     * @param \App\Entity\MeetingComment $comment
     * @param string $hash
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function approve(MeetingService $meetingService, MeetingComment $comment, string $hash): Response
    {
        if ($comment->getApproveHash() === $hash) {
            $meetingService->approveComment($comment);
        }

        return $this->redirectToRoute('meeting_view', [
            'url' => $comment->getMeeting()->getUrl()
        ]);
    }

    /**
     * @Route ("/deny/{id}/{hash}", name="comment_deny")
     * @IsGranted("DENY_COMMENT", subject="comment")
     * @param \App\Service\MeetingService $meetingService
     * @param \App\Entity\MeetingComment $comment
     * @param string $hash
     */
    public function deny(MeetingService $meetingService, MeetingComment $comment, string $hash)
    {
        $url = $comment->getMeeting()->getUrl();
        if ($comment->getApproveHash() === $hash) {
            $meetingService->denyComment($comment);
        }

        return $this->redirectToRoute('meeting_view', [
            'url' => $url
        ]);
    }


}
