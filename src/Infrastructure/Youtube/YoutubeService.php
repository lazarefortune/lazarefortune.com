<?php

namespace App\Infrastructure\Youtube;

use App\Domain\Course\Entity\Course;
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
        private readonly string $apiKey,
        private readonly string $youtubeChannelID,
        private readonly EntityManagerInterface $em,
        private readonly CourseTransformer      $transformer,
        private readonly HttpClientInterface $client
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
        $youtube->thumbnails->set( $video->getId(), $this->transformer->thumbnailData( $course ) );

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
     * @return int Nombre d'abonnés
     * @throws TransportExceptionInterface
     * @throws ServerExceptionInterface
     * @throws RedirectionExceptionInterface
     * @throws DecodingExceptionInterface
     * @throws ClientExceptionInterface
     */
    public function getSubscribersCount() : int
    {
        $url = sprintf(
            'https://www.googleapis.com/youtube/v3/channels?part=statistics&id=%s&key=%s',
            $this->youtubeChannelID,
            $this->apiKey
        );

        $response = $this->client->request('GET', $url);
        $data = $response->toArray();

        return $data['items'][0]['statistics']['subscriberCount'];
    }
}