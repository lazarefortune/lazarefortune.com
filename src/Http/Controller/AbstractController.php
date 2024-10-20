<?php

namespace App\Http\Controller;

use App\Domain\Auth\Core\Entity\User;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

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
}