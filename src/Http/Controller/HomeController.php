<?php

namespace App\Http\Controller;

use App\Domain\Application\Entity\Content;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use App\Domain\Course\Repository\TechnologyRepository;
use App\Domain\History\Entity\Progress;
use App\Domain\History\Service\HistoryService;
use App\Domain\Newsletter\Entity\NewsletterSubscriber;
use App\Domain\Newsletter\Exception\AlreadySubscribedException;
use App\Domain\Newsletter\Form\NewsletterSubscriberType;
use App\Domain\Newsletter\Repository\NewsletterSubscriberRepository;
use App\Domain\Newsletter\Service\NewsletterService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly TechnologyRepository $technologyRepository,
        private readonly HistoryService         $historyService
    )
    {
    }

    #[Route( '/', name: 'home' )]
    public function index(Request $request, NewsletterService $newsletterService) : Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $technologies = $this->technologyRepository->findAllWithContentCount();

        $subscriber = new NewsletterSubscriber();

        $newsletterForm = $this->createForm(NewsletterSubscriberType::class, $subscriber);

        $newsletterForm->handleRequest($request);

        if ($newsletterForm->isSubmitted() && $newsletterForm->isValid()) {
            try {
                $newsletterService->subscribe($subscriber);
                $this->addFlash('success', 'Vous êtes abonné à la newsletter!');
            } catch (AlreadySubscribedException $e) {
                $this->addFlash('info', $e->getMessage());
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue, veuillez réessayer.');
            }
        }

        if ( $user ) {
            $watchlist = $this->historyService->getLastWatchedContent( $user , 4);
            $excluded = array_map( fn ( Progress $progress ) => $progress->getContent()->getId(), $watchlist );

            $content = $this->em->getRepository( Content::class )
                ->findLatest( 12, $user->isPremium() )
                ->andWhere( 'c INSTANCE OF ' . Course::class . ' OR c INSTANCE OF ' . Formation::class );
            if ( !empty( $excluded ) ) {
                $content = $content->andWhere( 'c.id NOT IN (:ids)' )->setParameter( 'ids', $excluded );
            }

            return $this->render( 'pages/public/index-logged.html.twig' , [
                'latest_content' => $content,
                'watchlist' => $watchlist,
                'technologies' => $technologies,
                'newsletterForm' => $newsletterForm->createView(),
            ]);
        } else {
            $content = $this->em->getRepository( Content::class )
                ->findLatest( 12, false )
                ->andWhere( 'c INSTANCE OF ' . Course::class . ' OR c INSTANCE OF ' . Formation::class );

            return $this->render( 'pages/public/index.html.twig' , [
                'latest_content' => $content,
                'technologies' => $technologies,
                'newsletterForm' => $newsletterForm->createView(),
            ]);
        }
    }

    #[Route( '/mauvaise-region', name: 'access_denied' )]
    public function country() : Response
    {
        return $this->render( 'pages/public/country-access-denied.html.twig');
    }

    #[Route( '/a-propos', name: 'about' )]
    public function about() : Response
    {
        return $this->render( 'pages/public/about.html.twig');
    }

    #[Route( '/ui', name: 'ui' )]
    public function ui() : Response
    {
        return $this->render( 'pages/public/ui.html.twig' );
    }

    #[Route( '/message', name: 'message' )]
    public function message() : Response
    {
        $this->addFlash( 'success', 'Votre message a bien été envoyé' );
        return $this->render( 'pages/public/message.html.twig' );
    }

    #[Route( '/bienvenue', name: 'welcome' )]
    public function welcome() : Response
    {

        /*
        if ( $optionService->get( WelcomeModel::SITE_INSTALLED_NAME ) ) {
            return $this->redirectToRoute( 'app_home' );
        }

        $welcomeForm = $this->createForm( WelcomeForm::class, new WelcomeModel() );
        $welcomeForm->handleRequest( $request );

        if ( $welcomeForm->isSubmitted() && $welcomeForm->isValid() ) {
            $data = $welcomeForm->getData();

            $siteTitle = new Option( WelcomeModel::SITE_TITLE_LABEL, WelcomeModel::SITE_TITLE_NAME, $data->getSiteTitle(), TextType::class );
            $siteInstalled = new Option( WelcomeModel::SITE_INSTALLED_LABEL, WelcomeModel::SITE_INSTALLED_NAME, true, CheckboxType::class );

            $user = new User();
            $user->setFullname( $data->getFullname() );
            $user->setEmail( $data->getUsername() );
            $user->setRoles( ['ROLE_SUPER_ADMIN'] );
            $user->setPassword( $passwordHasher->hashPassword(
                $user,
                $data->getPassword()
            ) )
                ->setIsVerified( true )
                ->setCgu( true );

            $entityManager->persist( $siteTitle );
            $entityManager->persist( $siteInstalled );
            $entityManager->persist( $user );
            $entityManager->flush();

            $this->addFlash( 'success', 'Bienvenue sur votre nouveau site !' );
            return $this->redirectToRoute( 'app_success_installed' );
            */
            return new Response();
    }


    #[Route( '/installe', name: 'success_installed' )]
    public function successInstalled() : Response
    {
        return $this->render( 'pages/public/success_installed.html.twig' );
    }

    #[Route( '/conditions-generales-utilisation', name: 'cgu' )]
    public function cgu() : Response
    {
        return $this->render( 'pages/public/cgu.html.twig' );
    }

    #[Route( '/mentions-legales', name: 'mentions_legales' )]
    public function legalNotice() : Response
    {
        return $this->render( 'pages/public/mentions_legales.html.twig' );
    }

    #[Route( '/politique-confidentialite', name: 'politique_confidentialite' )]
    public function privacyPolicy() : Response
    {
        return $this->render( 'pages/public/politique_confidentialite.html.twig' );
    }

    #[Route( '/valentine', name: 'valentine' )]
    public function valentines() : Response
    {
        return $this->render( 'pages/public/valentines.html.twig' );
    }
}
