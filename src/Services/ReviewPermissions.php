<?php

namespace App\Services;

use App\Entity\ReviewInterface;
use App\Entity\UserInterface;

final class ReviewPermissions
{
    private const MODERATOR_USER_IDS = [
        8007, // Odrl
        5541, // mak
        7774, // Deathlysteve
        1190, // footlong  
    ];

    public function isModerator(UserInterface $user): bool
    {
        return in_array(
            (int) $user->getId(),
            self::MODERATOR_USER_IDS,
            true
        );
    }

    public function canEdit(
        UserInterface $user,
        ReviewInterface $review
    ): bool {
        return (int) $review->getUser()->getId() === (int) $user->getId()
            || $this->isModerator($user);
    }
}
