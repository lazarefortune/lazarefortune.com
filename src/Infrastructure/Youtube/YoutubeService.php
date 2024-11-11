<?php

namespace App\Infrastructure\Youtube;

use App\Domain\Course\Entity\Course;
use App\Domain\Youtube\Exception\NotFoundYoutubeAccount;
use App\Domain\Youtube\Repository\YoutubeSettingRepository;
use App\Domain\Youtube\Service\YoutubeAdminService;
use App\Infrastructure\Youtube\Transformer\CourseTransformer;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\Exception;
use Google\Service\YouTube;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class YoutubeService
{
    public function __construct(
        private readonly \Google_Client         $googleClient,
        private readonly EntityManagerInterface $em,
        private readonly CourseTransformer      $transformer,
        private readonly HttpClientInterface $client,
        private readonly YoutubeAdminService $youtubeAdminService
    )
    {
    }

    private function getAuthenticatedClient(): YouTube
    {
        // Rafraîchit le token si nécessaire
        if ($this->googleClient->isAccessTokenExpired()) {
            $this->googleClient->fetchAccessTokenWithRefreshToken($this->googleClient->getRefreshToken());
        }
        return new YouTube($this->googleClient);
    }

    public function uploadVideo(int $courseId, array $accessToken): string
    {
        $course = $this->em->getRepository( Course::class )->find( $courseId );
        if ( null === $course ) {
            throw new \RuntimeException( "Impossible de trouver le cours #{$courseId}" );
        }
        $this->googleClient->setAccessToken( $accessToken );
        $youtube = new \Google_Service_YouTube( $this->googleClient );
        $youtubeId = $course->getYoutubeId();
        $video = $this->transformer->transform( $course );
        $parts = 'snippet,status';
        if ( $youtubeId ) {
            $video = $youtube->videos->update( $parts, $video );
        } else {
            $video = $youtube->videos->insert( $parts, $video, $this->transformer->videoData( $course ) );
            $course->setYoutubeId( $video->getId() );
            $this->em->flush();
        }

        // On met à jour la thumbnail
        $thumbnailData = $this->transformer->thumbnailData($course);
        if ($thumbnailData) {
            $youtube->thumbnails->set($video->getId(), $thumbnailData);
        }

        return $video->getId();
    }

    /**
     * Récupère la durée d'une vidéo Youtube en secondes
     * @param string $courseId
     * @param array $accessToken
     * @return int Durée de la vidéo en secondes
     * @throws Exception
     * @throws \Exception
     */
    public function getVideoDuration( string $courseId, array $accessToken ) : int
    {
        $course = $this->em->getRepository( Course::class )->find( $courseId );
        if ( null === $course ) {
            throw new \RuntimeException( "Impossible de trouver le cours #{$courseId}" );
        }

        $this->googleClient->setAccessToken( $accessToken );
        $youtube = new \Google_Service_YouTube( $this->googleClient );
        $video = $youtube->videos->listVideos( 'contentDetails', ['id' => $course->getYoutubeId()] );
        $duration = $video->getItems()[0]->getContentDetails()->getDuration();
        $interval = new DateInterval( $duration );

        return ( $interval->h * 3600 ) + ( $interval->i * 60 ) + $interval->s;
    }

    /**
     * Récupère le nombre d'abonnés d'une chaîne YouTube
     * @throws NotFoundYoutubeAccount
     */
    public function getSubscribersCount(): int
    {
        $youtubeSetting = $this->youtubeAdminService->getAccount();
        if (!$youtubeSetting || !$youtubeSetting->getAccessToken()) {
            throw new NotFoundYoutubeAccount('Aucun compte YouTube n\'est lié pour récupérer les informations de la chaîne.');
        }

        // Rafraîchit le token si nécessaire
        $this->googleClient->setAccessToken($youtubeSetting->getAccessToken());
        if ($this->googleClient->isAccessTokenExpired() && $youtubeSetting->getRefreshToken()) {
            $newAccessToken = $this->googleClient->fetchAccessTokenWithRefreshToken($youtubeSetting->getRefreshToken());
            if (isset($newAccessToken['access_token'])) {
                $youtubeSetting->setAccessToken(json_encode($newAccessToken));
                $this->em->flush();
                $this->googleClient->setAccessToken($newAccessToken);
            } else {
                throw new \RuntimeException('Impossible de rafraîchir le token d\'accès.');
            }
        }

        // Créer une instance du client YouTube
        $youtube = new YouTube($this->googleClient);

        try {
            // Vérifie si un ID de chaîne est disponible
            $channelId = $youtubeSetting->getChannelId();

            if (!$channelId || preg_match('/\s/', $channelId)) {
                // Si aucun channelId n'est présent, essaye de récupérer le channelId
                $response = $youtube->channels->listChannels('snippet', [
                    'mine' => true
                ]);
                if (count($response->getItems()) > 0) {
                    $channelId = $response->getItems()[0]->getId();
                    $youtubeSetting->setChannelId($channelId);
                    $this->em->flush();
                } else {
                    throw new NotFoundYoutubeAccount("Aucune chaîne YouTube trouvée pour l'utilisateur authentifié.");
                }
            }

            // Appelle l'API pour obtenir les statistiques de la chaîne
            $response = $youtube->channels->listChannels('statistics', [
                'id' => $channelId
            ]);

            if (count($response->getItems()) === 0) {
                throw new \RuntimeException("Impossible de trouver la chaîne YouTube avec l'ID fourni : " . $channelId);
            }

            // Ajout d'une vérification supplémentaire pour s'assurer que les statistiques existent
            $statistics = $response->getItems()[0]->getStatistics();
            if (!$statistics || !isset($statistics['subscriberCount'])) {
                throw new \RuntimeException("Les statistiques de la chaîne YouTube n'ont pas pu être récupérées correctement.");
            }

            // Retourne le nombre d'abonnés de la chaîne
            return (int)$statistics->getSubscriberCount();
        } catch (Exception $e) {
            throw new \RuntimeException('Erreur lors de la récupération des abonnés : ' . $e->getMessage());
        }
    }

}