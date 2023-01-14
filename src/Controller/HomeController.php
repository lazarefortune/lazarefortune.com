<?php

namespace App\Controller;

use App\Form\ContactType;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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

            try {
                $mailerService->sendMail(
                    $contact->getEmail(),
                    $contact->getName(),
                    $contact->getSubject(),
                    $contact->getMessage()
                );
                $this->addFlash('success', 'Votre message a bien été envoyé');
            } catch (\Exception $e) {
                $this->addFlash('danger', 'Une erreur est survenue lors de l\'envoi de votre message');
            }

            $this->redirectToRoute('app_home', ['_fragment' => 'contact']);
        }

        return $this->render('home/index.html.twig', [
            'formContact' => $formContact->createView(),
        ]);
    }

}
