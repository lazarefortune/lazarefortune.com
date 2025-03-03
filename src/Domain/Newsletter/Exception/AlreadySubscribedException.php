<?php

namespace App\Domain\Newsletter\Exception;

class AlreadySubscribedException extends \Exception
{
    protected $message = 'Vous êtes déjà abonné à la newsletter.';

    public function __construct($message = null)
    {
        if ($message) {
            $this->message = $message;
        }
        parent::__construct($this->message);
    }
}