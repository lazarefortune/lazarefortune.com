<?php

namespace App\Domain\Application\EventListener;

use App\Domain\Application\Service\OptionManager;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class ConfigListener
{
    public function __construct(
        private readonly OptionManager $optionService,
        private readonly Environment   $twig,
    )
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function onKernelRequest( RequestEvent $event ) : void
    {
        // TODO: Si besoin d'utiliser le nom de l'application en BDD dans les templates, décommenter les lignes ci-dessous
//        $appName = $this->optionService->getApplicationName();
//        $this->twig->addGlobal( 'app_name', $appName );
    }
}
