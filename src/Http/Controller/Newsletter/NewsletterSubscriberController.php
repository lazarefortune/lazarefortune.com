<?php

namespace App\Http\Controller\Newsletter;

use App\Domain\Newsletter\Entity\NewsletterSubscriber;
use App\Domain\Newsletter\Form\NewsletterSubscriberType;
use App\Domain\Newsletter\Repository\NewsletterSubscriberRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class NewsletterSubscriberController extends AbstractController
{
    #[Route('/newsletter/inscription', name: 'newsletter_subscribe')]
    public function subscribe(
        Request $request,
        EntityManagerInterface $em,
        NewsletterSubscriberRepository $subscriberRepository
    ): Response
    {
        $subscriber = new NewsletterSubscriber();

        $form = $this->createForm(NewsletterSubscriberType::class, $subscriber);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existing = $subscriberRepository->findOneBy(['email' => $subscriber->getEmail()]);

            if ($existing) {
                if ($existing->isSubscribed()) {
                    $this->addFlash('info', 'Vous êtes déjà abonné à la newsletter.');
                } else {
                    $existing->setSubscribed(true);
                    $em->flush();
                    $this->addFlash('success', 'Votre abonnement a été réactivé.');
                }
            } else {
                $em->persist($subscriber);
                $em->flush();
                $this->addFlash('success', 'Vous êtes abonné à la newsletter!');
            }

            return $this->redirectToRoute('app_home');
        }

        // 5) Rendre la vue pour afficher le formulaire
        return $this->render('newsletter/subscribe.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
