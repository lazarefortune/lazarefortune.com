<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function accueil( Request $request , MailerService $mailerService) : Response
    {
        $formContact = $this->createForm(ContactType::class);
        $formContact->handleRequest($request);

        if( $formContact->isSubmitted() && $formContact->isValid() ) {
            $contact = $formContact->getData();
//             dd($contact);
            // Envoi du mail
            $mailerService->sendMail(
                $contact->getEmail(),
                $contact->getName(),
                $contact->getSubject(),
                $contact->getMessage()
            );

            $this->addFlash('success', 'Votre message a bien été envoyé');
             // redirect to route app_home with #contact
            $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'name' => 'John Doe',
            'formContact' => $formContact->createView(),
        ]);
    }

    #[Route('/email', name: 'app_email')]
    public function sendMail(MailerInterface $mailer) : Response
    {
        $email = (new Email())
            ->from('test@test.com')
            ->to('testeur@test.com')
            ->subject('Test email')
            ->text('Sending emails is fun again!');

        $mailer->send($email);

        return new Response('Email envoyé');
    }

}
