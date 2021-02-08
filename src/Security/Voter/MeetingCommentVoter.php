<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MeetingCommentVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['APPROVE_COMMENT', 'DENY_COMMENT'])
            && $subject instanceof \App\Entity\MeetingComment;
    }

    /**
     * @param string $attribute
     * @param \App\Entity\MeetingComment $subject
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        switch ($attribute) {
            case 'APPROVE_COMMENT':
                // подтверждать комментарии можно только в своей встрече, а также комментарий должен быть ещё не подтвежден
                return !$subject->getIsApproved() && ($user->getId() === $subject->getMeeting()->getUser()->getId());
            case 'DENY_COMMENT':
                // отвергать/удалять комментарии можно только в своей встрече ну или свой личный комментарий
                return $user->getId() === $subject->getMeeting()->getUser()->getId()
                    || $user->getId() === $subject->getId();
        }

        return false;
    }
}
