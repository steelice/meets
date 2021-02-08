<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class MeetingVoter extends Voter
{
    protected function supports($attribute, $subject)
    {
        return in_array($attribute, ['MEETING_CREATE', 'ADD_COMMENT', 'MEETING_VISIT'])
            && $subject instanceof \App\Entity\Meeting;
    }

    /**
     * @param string $attribute
     * @param \App\Entity\Meeting $subject
     * @param \Symfony\Component\Security\Core\Authentication\Token\TokenInterface $token
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        $user = $token->getUser();
        if (!$user instanceof UserInterface) {
            return false;
        }

        /**
         * Пока что всем авторизованным пользователям всё разрешено
         */

        return true;
    }
}
