<?php

namespace App\Http\Security;

use App\Domain\Application\Entity\Content;
use App\Domain\Auth\Core\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class ContentVoter extends Voter
{
    final public const PROGRESS = 'progress';
    final public const EDIT = 'edit';
    final public const DELETE = 'delete';

    public function __construct(
        private readonly Security $security
    )
    {
    }

    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return in_array($attribute, [
                self::PROGRESS,
                self::EDIT,
                self::DELETE,
            ]) && $subject instanceof Content;
    }

    /**
     * @param Content $subject
     */
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $content = $subject;

        return match ( $attribute ) {
            self::PROGRESS => $this->canProgress( $content, $user ),
            self::EDIT, self::DELETE => $this->canEditOrDelete( $content, $user ),
        };
    }

    private function canProgress(Content $content, User $user): bool
    {
        $contentIsPublished = !$content->isScheduled() && $content->isOnline();
        return $user->isPremium() || $contentIsPublished;
    }

    private function canEditOrDelete( Content $content, User $user): bool
    {
        return $this->security->isGranted( 'ROLE_SUPER_ADMIN' )
            || $content->getAuthor()->getId() === $user->getId();
    }
}