<?php

namespace App\Domain\Holiday\Voter;

use App\Domain\Auth\Entity\User;
use App\Domain\Holiday\Entity\Holiday;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

class HolidayVoter extends Voter
{
    const EDIT = 'HOLIDAY_EDIT';
    const DELETE = 'HOLIDAY_DELETE';

    /**
     * @inheritDoc
     */
    protected function supports( string $attribute, mixed $subject ) : bool
    {
        if ( !in_array( $attribute, [self::EDIT, self::DELETE] ) ) {
            return false;
        }

        if ( !$subject instanceof Holiday ) {
            return false;
        }

        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute( string $attribute, mixed $subject, TokenInterface $token ) : bool
    {
        $user = $token->getUser();

        if ( !$user instanceof User ) {
            return false;
        }

        /** @var Holiday $holiday */
        $holiday = $subject;

        switch ( $attribute ) {
            case self::EDIT:
                return $this->canEdit( $holiday, $user );
            case self::DELETE:
                return $this->canDelete( $holiday, $user );
        }

        throw new \LogicException( 'This code should not be reached!' );
    }

    private function canEdit( Holiday $holiday, User $user ) : bool
    {
        $today = new \DateTime();

        return  $this->isAllowed($user) && $holiday->getEndDate() > $today;
    }

    private function canDelete( Holiday $holiday, User $user ) : bool
    {
        $today = new \DateTime();

        return $this->isAllowed($user) && $holiday->getStartDate() > $today;
    }

    private function isAllowed( User $user ) : bool
    {
        $allowedRoles = ['ROLE_ADMIN', 'ROLE_MANAGER', 'ROLE_SUPER_ADMIN'];

        return !empty(array_intersect( $user->getRoles(), $allowedRoles ));
    }
}