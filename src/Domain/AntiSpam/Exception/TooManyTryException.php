<?php

namespace App\Domain\AntiSpam\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\WithHttpStatus;

#[WithHttpStatus(Response::HTTP_FORBIDDEN)]
class TooManyTryException extends \Exception
{
    private string $newKey;

    public function __construct(string $newKey = "")
    {
        parent::__construct("Too many tries.");
        $this->newKey = $newKey;
    }

    public function getNewKey(): string
    {
        return $this->newKey;
    }
}