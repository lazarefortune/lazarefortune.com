<?php

namespace App\Domain\Youtube\Exception;

class NotFoundYoutubeAccount extends \Exception
{
    protected $message = 'Youtube account not found';
    protected $code = 404;
}