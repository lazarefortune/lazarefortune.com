<?php

namespace App\Http\Security;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Newsletter\Entity\Newsletter;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class NewsletterVoter extends Voter
{
    final public const CREATE = 'NEWSLETTER_CREATE';
    final public const EDIT = 'NEWSLETTER_EDIT';
    final public const DELETE = 'NEWSLETTER_DELETE';

    protected function supports( string $attribute, mixed $subject ) : bool
    {
        return in_array($attribute, [
            self::CREATE,
            self::EDIT,
            self::DELETE,
        ]);
    }

    protected function voteOnAttribute( string $attribute, mixed $subject, TokenInterface $token ) : bool
    {
        /** @var User $user */
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        if ($attribute === self::CREATE) {
            return true;
        }

        if (!$subject instanceof Newsletter) {
            return false;
        }

        if ($attribute === self::EDIT || $attribute === self::DELETE) {
            return $subject->getSendAt() > new \DateTime();
        }

        return false;
    }
}