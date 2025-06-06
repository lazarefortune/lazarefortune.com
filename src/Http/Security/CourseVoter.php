<?php

namespace App\Http\Security;


use App\Domain\Auth\Core\Entity\User;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class CourseVoter extends Voter
{
    final public const DOWNLOAD_SOURCE = 'DOWNLOAD_SOURCE';
    final public const DOWNLOAD_VIDEO = 'DOWNLOAD_VIDEO';

    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [
            self::DOWNLOAD_SOURCE,
            self::DOWNLOAD_VIDEO,
        ]);
    }

    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        return $user instanceof User && $user->isPremium();
    }
}