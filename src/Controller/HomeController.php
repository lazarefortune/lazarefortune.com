<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\MailerService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home( Request $request , MailerService $mailerService, EntityManagerInterface $entityManager) : Response
    {
        $formContact = $this->createForm(ContactType::class);
        $formContact->handleRequest($request);

        if( $formContact->isSubmitted() && $formContact->isValid() ) {

            $contact = $formContact->getData();
            $entityManager->persist($contact);
            $entityManager->flush();

            try {
                $mailerService->sendMail(
                    $contact->getEmail(),
                    $contact->getSubject(),
                    'emails/contact/thank-you.html.twig',
                    [
                        'name' => $contact->getName(),
                        'subject' => $contact->getSubject(),
                        'message' => $contact->getMessage(),
                    ]
                );

                $this->addFlash('success', 'Votre message a bien été envoyé');
            } catch ( TransportExceptionInterface|\Exception $e ) {
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi de votre message');
            }

            // send mail to admin
            try {
                $mailerService->sendMail(
                    $this->getParameter('app.mailer.admin'),
                    "Nouveau message de " . $contact->getName(),
                    'emails/contact/new-message.html.twig',
                    [
                        'email' => $contact->getEmail(),
                        'name' => $contact->getName(),
                        'subject' => $contact->getSubject(),
                        'message' => $contact->getMessage(),
                    ]
                );
            }
            catch ( TransportExceptionInterface|\Exception $e ) {
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi de votre message');
            }

            $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }


        return $this->render('home/index.html.twig', [
            'formContact' => $formContact->createView(),
        ]);
    }

    #[Route('/v2', name: 'app_home_v2')]
    public function home_version2() : Response
    {
        return $this->render('home/index_v2.html.twig');
    }

}
