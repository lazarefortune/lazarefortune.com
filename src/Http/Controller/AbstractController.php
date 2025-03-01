<?php

namespace App\Http\Controller;

use App\Domain\Auth\Core\Entity\User;
use App\Infrastructure\Queue\Message\ServiceMethodMessage;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

abstract class AbstractController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    protected function getUserOrThrow() : User
    {
        /** @var User $user */
        $user = $this->getUser();

        if ( !$user ) {
            throw $this->createAccessDeniedException( 'Vous devez être connecté pour accéder à cette page' );
        }

        return $user;
    }

    /**
     * Redirect to the previous page if possible, or to the given route
     * @param string $route
     * @param array<string, mixed> $params
     */
    protected function redirectBack( string $route, array $params = [] ) : RedirectResponse
    {
        /** @var RequestStack $stack */
        $stack = $this->container->get( 'request_stack' );
        $request = $stack->getCurrentRequest();
        if ( $request && $request->server->get( 'HTTP_REFERER' ) ) {
            return $this->redirect( $request->server->get( 'HTTP_REFERER' ) );
        }

        return $this->redirectToRoute( $route, $params );
    }

    /**
     * Lance la méthode d'un service de manière asynchrone.
     */
    protected function dispatchMethod(
        MessageBusInterface $messageBus,
        string $service,
        string $method,
        array $params = [],
    ): Envelope {
        return $messageBus->dispatch(new ServiceMethodMessage($service, $method, $params), []);
    }

    /**
     * Show errors as flash messages
     */
    protected function flashErrors( FormInterface $form ) : void
    {
        /** @var FormError[] $errors */
        $errors = $form->getErrors();
        $messages = [];
        foreach ( $errors as $error ) {
            $messages[] = $error->getMessage();
        }
        $this->addFlash( 'error', implode( "\n", $messages ) );
    }

    /**
     * Check if the user has a specific role
     * @deprecated
     */
    protected function hasRole(User $user, string $role): bool
    {
        trigger_error('The '.__METHOD__.' method is deprecated. Use Symfony security voters instead.', E_USER_DEPRECATED);
        $roles = $user->getRoles();
        $roleHierarchy = [
            'ROLE_SUPER_ADMIN' => ['ROLE_AUTHOR', 'ROLE_ADMIN', 'ROLE_ALLOWED_TO_SWITCH'],
            'ROLE_AUTHOR' => ['ROLE_ADMIN'],
            'ROLE_ADMIN' => ['ROLE_ALLOWED_TO_SWITCH'],
        ];

        if (in_array($role, $roles)) {
            return true;
        }

        foreach ($roles as $userRole) {
            if (isset($roleHierarchy[$userRole]) && in_array($role, $roleHierarchy[$userRole])) {
                return true;
            }
        }

        return false;
    }
}