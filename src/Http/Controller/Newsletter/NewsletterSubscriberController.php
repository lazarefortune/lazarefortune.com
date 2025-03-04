<?php

namespace App\Http\Controller\Newsletter;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Newsletter\Repository\NewsletterSubscriberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

class NewsletterSubscriberController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $userRepository
    ) {
    }

    #[Route('/newsletter/inscription', name: 'newsletter_subscribe')]
    #[IsGranted('ROLE_USER')]
    public function subscribeUser(): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $user->setNewsletterSubscribed(true);
        $this->em->flush();
        return $this->redirectToRoute('app_account_profile');
    }

    #[Route('/newsletter/desinscription/subscriber/{token}', name: 'newsletter_unsubscribe_subscriber')]
    public function unsubscribeSubscriber(
        string $token,
        NewsletterSubscriberRepository $repository,
        EntityManagerInterface $em
    ): Response {
        $subscriber = $repository->findOneBy(['unsubscribeToken' => $token]);
        if ($subscriber) {
            $subscriber->setSubscribed(false);
            $em->flush();
            $message = 'Vous avez bien été désabonné de la newsletter.';
        } else {
            $message = 'Aucun abonnement trouvé pour ce token.';
        }
        return new Response($message);
    }

    #[Route('/newsletter/desinscription/user/{token}', name: 'newsletter_unsubscribe_user')]
    public function unsubscribeUser(
        string $token,
        UserRepository $repository,
        EntityManagerInterface $em
    ): Response {
        $user = $repository->findOneBy(['unsubscribeNewsletterToken' => $token]);
        if ($user) {
            $user->setNewsletterSubscribed(false);
            $em->flush();
            $message = 'Vous avez bien été désabonné de la newsletter.';
        } else {
            $message = 'Aucun abonnement trouvé pour ce token.';
        }
        return new Response($message);
    }


}
