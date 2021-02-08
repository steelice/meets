<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Entity\MeetingComment;
use App\Form\MeetingCommentType;
use App\Form\MeetingType;
use App\Service\MeetingService;
use App\Repository\MeetingRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MeetController extends AbstractController
{
    /**
     * Список встреч на главной
     *
     * @Route("/", name="index")
     */
    public function index(Request $request, MeetingRepository $meetingRepository): Response
    {
        $skip = max(0, $request->query->getInt('skip', 0));

        $meetings = $meetingRepository->getMeetingsPaginator($skip);
        return $this->render('meet/index.html.twig', [
            'meetings' => $meetings,
            'previous' => $skip - MeetingRepository::MEETING_PER_PAGE,
            'next' => min(count($meetings), $skip + MeetingRepository::MEETING_PER_PAGE),
        ]);
    }


    /**
     * Создает встречу
     *
     * @IsGranted("ROLE_USER")
     * @Route("/lets-meet", name="meeting_create")
     */
    public function create(Request $request, MeetingService $meetingService)
    {
        $meeting = new Meeting();
        $meeting->setUser($this->getUser());
        $form = $this->createForm(MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $meetingService->create($meeting);

            return $this->redirectToRoute('meeting_view', ['url' => $meeting->getUrl()]);
        }


        return $this->render('meet/create.html.twig', [
            'meetingForm' => $form->createView(),
        ]);
    }

    /**
     * Просмотр встречи, её комментирование
     *
     * @Route ("/view/{url}/", name="meeting_view")
     */
    public function view(Request $request, Meeting $meeting, MeetingService $meetingService)
    {
        $comment = new MeetingComment();
        $comment->setMeeting($meeting);
        $commentForm = $this->createForm(MeetingCommentType::class, $comment);
        $commentForm->handleRequest($request);

        if ($commentForm->isSubmitted() && $commentForm->isValid()) {
            $this->denyAccessUnlessGranted('ADD_COMMENT', $meeting);

            $meetingService->addComment($comment, $this->getUser());

            return $this->redirectToRoute('meeting_view', [
                'url' => $meeting->getUrl()
            ]);
        }

        return $this->render('meet/view.html.twig', [
            'meeting' => $meeting,
            'commentForm' => $commentForm->createView(),
            'comments' => $meetingService->getComments($meeting, $this->getUser()),
            'imGoing' => $meetingService->isUserGoing($meeting, $this->getUser()),
        ]);
    }

    /**
     * Пометка о том, что пользователь придёт на встречу
     *
     * @Route ("/visit/{url}", name="meeting_visit", methods={"POST"})
     * @IsGranted ("MEETING_VISIT", subject="meeting")
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \App\Entity\Meeting $meeting
     * @param \App\Service\MeetingService $meetingService
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function visit(Request $request, Meeting $meeting, MeetingService $meetingService)
    {
        $meetingService->userGoing($meeting, $this->getUser());

        return $this->redirectToRoute('meeting_view', [
            'url' => $meeting->getUrl()
        ]);
    }


}
