<?php

namespace App\Domain\Auth;

use App\Domain\Auth\Entity\User;
use App\Infrastructure\AppConfigService;
use App\Infrastructure\Mailing\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use SymfonyCasts\Bundle\VerifyEmail\Model\VerifyEmailSignatureComponents;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

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
