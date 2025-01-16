<?php

namespace App\Http\Controller;

use App\Domain\Application\Entity\Content;
use App\Domain\Application\Entity\Option;
use App\Domain\Application\Form\WelcomeForm;
use App\Domain\Application\Model\WelcomeModel;
use App\Domain\Application\Service\OptionManager;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use App\Domain\Course\Entity\Technology;
use App\Domain\History\Entity\Progress;
use App\Domain\History\Service\HistoryService;
use App\Infrastructure\Spam\GeoLocationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly HistoryService         $historyService
    )
    {
    }

    #[Route( '/', name: 'home' )]
    public function index() : Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $technologies = $this->em->getRepository( Technology::class )->findAll();

        if ( $user ) {
            $watchlist = $this->historyService->getLastWatchedContent( $user );
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
            ]);
        } else {
            $content = $this->em->getRepository( Content::class )
                ->findLatest( 12, false )
                ->andWhere( 'c INSTANCE OF ' . Course::class . ' OR c INSTANCE OF ' . Formation::class );

            return $this->render( 'pages/public/index.html.twig' , [
                'latest_content' => $content,
                'technologies' => $technologies,
            ]);
        }


//        return $this->render( 'pages/public/index.html.twig');
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
        return $this->render( 'pages/ui.html.twig' );
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
}
