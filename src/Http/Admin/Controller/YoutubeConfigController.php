<?php

namespace App\Http\Admin\Controller;

use App\Domain\Youtube\Entity\YoutubeSetting;
use App\Domain\Youtube\Exception\NotFoundYoutubeAccount;
use App\Domain\Youtube\Repository\YoutubeSettingRepository;
use App\Domain\Youtube\Service\YoutubeAdminService;
use App\Http\Controller\AbstractController;
use App\Infrastructure\Youtube\YoutubeScopes;
use App\Infrastructure\Youtube\YoutubeService;
use Google_Client;
use Google_Service_YouTube;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Cache\ItemInterface;

#[IsGranted( "ROLE_SUPER_ADMIN" )]
#[Route( '/youtube', name: 'youtube_config_' )]
class YoutubeConfigController extends AbstractController
{
    public function __construct(
        private readonly YoutubeAdminService      $youtubeAdminService,
        private readonly YoutubeService           $youtubeService,
        private readonly YoutubeSettingRepository $youtubeSettingRepository
    )
    {
    }

    #[Route( '/', name: 'index', methods: ['GET'] )]
    public function settings( CacheItemPoolInterface $cache ) : Response
    {
        $settings = $this->youtubeAdminService->getAccount();

        $stats = [];
        if ( $settings && $settings->getGoogleId() ) {
            $stats = $cache->get('youtube.stats', function (ItemInterface $item) {
                $item->expiresAfter(3600);

                try {
                    return $this->youtubeService->getChannelStats();
                } catch (NotFoundYoutubeAccount $exception) {
                    $this->addFlash('error', $exception->getMessage());
                } catch (\Exception $e) {
                    $this->addFlash('error', 'Impossible de récupérer les stats YouTube');
                }
                return [];
            });
        }


        return $this->render( 'pages/admin/youtube/settings.html.twig', [
            'settings' => $settings,
            'stats' => $stats
        ] );
    }

    #[Route('/refresh-stats', name: 'refresh_stats', methods: ['GET'])]
    public function refreshStats(CacheItemPoolInterface $cache): Response
    {
        // Supprime l'item "youtube.stats" du cache
        $cache->deleteItem( 'youtube.stats' );

        // Message flash
        $this->addFlash( 'success', "Les statistiques YouTube ont été rafraîchies." );

        // Redirection vers la page principale
        return $this->redirectToRoute( 'admin_youtube_config_index' );
    }


    #[Route( '/link', name: 'link', methods: ['GET'] )]
    public function linkGoogleAccount( Google_Client $googleClient ) : Response
    {
        $redirectUri = $this->generateUrl( 'admin_youtube_config_callback', [], UrlGeneratorInterface::ABSOLUTE_URL );
        $googleClient->setRedirectUri( $redirectUri );
        $googleClient->setAccessType( 'offline' );
        // TODO: à améliorer
        // Force la demande de consentement
        $googleClient->setPrompt( 'consent' );
        $authUrl = $googleClient->createAuthUrl( implode( ' ', YoutubeScopes::UPLOAD ) );

        return $this->redirect( $authUrl );
    }


    #[Route( '/callback', name: 'callback', methods: ['GET'] )]
    public function googleCallback( Google_Client $googleClient, Request $request ) : Response
    {
        $code = $request->get( 'code' );

        if ( !$code ) {
            $this->addFlash( 'danger', "La connexion a échoué. Veuillez réessayer." );
            return $this->redirectToRoute( 'admin_youtube_config_index' );
        }

        $redirectUri = $this->generateUrl( 'admin_youtube_config_callback', [], UrlGeneratorInterface::ABSOLUTE_URL );
        $googleClient->setRedirectUri( $redirectUri );

        $accessToken = $googleClient->fetchAccessTokenWithAuthCode( $code );

        if ( isset( $accessToken['error'] ) ) {
            $this->addFlash( 'danger', "Erreur lors de la connexion : " . $accessToken['error_description'] );
            return $this->redirectToRoute( 'admin_youtube_config_index' );
        }

        $googleClient->setAccessToken( $accessToken );

        // Vérifie le jeton ID pour obtenir des informations de profil
        $payload = $googleClient->verifyIdToken();
        $email = $payload['email'] ?? null;
        $googleUserId = $payload['sub'] ?? null;

        // Récupère les infos de la chaîne
        $youtube = new Google_Service_YouTube($googleClient);
        $response = $youtube->channels->listChannels('snippet', ['mine' => true]);
        $channels = $response->getItems();
        if (count($channels) === 0) {
            $channelId = null;
            $channelName = null;
        } else {
            $channel = $channels[0];
            $channelId = $channel->getId();
            $channelName = $channel->getSnippet()->getTitle();
        }

        // Stockage du token d'accès dans `YoutubeSetting`
        $settings = $this->youtubeSettingRepository->findOneBy( [] ) ?? new YoutubeSetting();
        $settings->setAccessToken( $accessToken['access_token'] );
        $settings->setRefreshToken( $accessToken['refresh_token'] ?? null );

        $settings->setGoogleId( $googleUserId );
        #$settings->setGoogleId($googleClient->getClientId());
        $settings->setEmail( $email );
        $settings->setChannelId( null );
        $settings->setChannelName( $channelName );

        $this->youtubeSettingRepository->save( $settings, true );

        $this->addFlash( 'success', "Le compte Google a été lié avec succès." );
        return $this->redirectToRoute( 'admin_youtube_config_index' );
    }

    #[Route( '/unlink', name: 'unlink', methods: ['GET'] )]
    public function unlinkGoogleAccount() : Response
    {
        $settings = $this->youtubeSettingRepository->findOneBy( [] );

        if ( $settings ) {
            // Supprime les tokens en les définissant à null
            $settings->setAccessToken( null );
            $settings->setRefreshToken( null );
            $settings->setGoogleId( null );
            $settings->setChannelId( null );
            $settings->setChannelName( null );
            $this->youtubeSettingRepository->save( $settings, true );

            $this->addFlash( 'success', "Le compte Google a été délié avec succès." );
        } else {
            $this->addFlash( 'danger', "Aucun compte Google n'est lié." );
        }

        return $this->redirectToRoute( 'admin_youtube_config_index' );
    }


}