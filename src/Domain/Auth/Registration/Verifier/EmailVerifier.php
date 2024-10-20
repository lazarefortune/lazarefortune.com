<?php

namespace App\Domain\Auth\Registration\Verifier;

use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class EmailVerifier
{
    private const APP_VERIFY_EMAIL_ROUTE = 'app_verify_email';

    public function __construct(
        private readonly VerifyEmailHelperInterface $verifyEmailHelper,
    )
    {
    }

    public function generateSignature( UserInterface $user ) : VerifyEmailSignatureComponents
    {
        return $this->verifyEmailHelper->generateSignature(
            self::APP_VERIFY_EMAIL_ROUTE,
            $user->getId(),
            $user->getEmail(),
            ['id' => $user->getId()]
        );
    }
}
