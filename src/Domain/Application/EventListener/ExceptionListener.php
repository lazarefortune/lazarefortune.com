<?php

namespace App\Domain\Application\EventListener;

use App\Domain\Application\Service\DatabaseService;
use Doctrine\DBAL\ConnectionException;
use Doctrine\DBAL\Exception\TableNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\Routing\RouterInterface;

class ExceptionListener
{
    public function __construct( private readonly DatabaseService $databaseService, private RouterInterface $router )
    {
    }

    public function onKernelException( ExceptionEvent $event ) : void
    {
        $exception = $event->getThrowable();

        if ( $exception instanceof ConnectionException || $exception instanceof TableNotFoundException ) {
            $this->databaseService->createDatabase();

            $event->setResponse( new RedirectResponse(
                $this->router->generate( 'app_welcome' )
            ) );
        }
    }
}