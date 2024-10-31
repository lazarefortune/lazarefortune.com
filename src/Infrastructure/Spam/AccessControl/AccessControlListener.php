<?php

namespace App\Infrastructure\Spam\AccessControl;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Routing\RouterInterface;

class AccessControlListener
{
    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly AccessControlService $accessControlService,
        private readonly RouterInterface $router,
        private readonly string $env
    ){}

    public function onKernelRequest(RequestEvent $event): void
    {
        if ($this->env === 'dev') {
            return;
        }

        $request = $this->requestStack->getCurrentRequest();
        $userIp = $request->getClientIp();

        $currentRoute = $request->attributes->get('_route');
        if ($currentRoute === 'app_access_denied') {
            return;
        }

        if (!$this->accessControlService->isAccessAllowed($userIp)) {
            $accessDeniedUrl = $this->router->generate('app_access_denied');
            $event->setResponse(new RedirectResponse($accessDeniedUrl));
        }
    }
}