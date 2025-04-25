<?php

namespace App\Infrastructure\Youtube;

use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use App\Infrastructure\Youtube\Transformer\CourseTransformer;
use App\Infrastructure\Youtube\Transformer\FormationTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Google\Service\Exception;
use DateInterval;

class YoutubeUploaderService
{
    public function __construct(
        private readonly \Google_Client         $googleClient,
        private readonly EntityManagerInterface $em,
        private readonly CourseTransformer      $courseTransformer,
        private readonly FormationTransformer   $formationTransformer,

    ) {
    }

    public function upload(int $courseId, array $accessToken): string
    {
        $course = $this->em->getRepository(Course::class)->find($courseId);
        if (null === $course) {
            throw new \RuntimeException("Impossible de trouver le cours #{$courseId}");
        }
        $this->googleClient->setAccessToken($accessToken);
        $youtube = new \Google_Service_YouTube($this->googleClient);
        $youtubeId = $course->getYoutubeId();
        $video = $this->courseTransformer->transform($course);
        $parts = 'snippet,status';
        if ($youtubeId) {
            $video = $youtube->videos->update($parts, $video);
            $duration = $this->getVideoDuration($courseId, $accessToken);
            $course->setDuration($duration);
            $this->em->flush();
        } else if ($course->getVideoPath()) {
            $video = $youtube->videos->insert($parts, $video, $this->courseTransformer->videoData($course));
            $course->setYoutubeId($video->getId());
            $this->em->flush();
        } else {
            throw new \RuntimeException("Impossible de mettre à jour la vidéo #{$courseId} sur Youtube");
        }

        // On met à jour la thumbnail
        if ($course->getYoutubeThumbnail()) {
            $youtube->thumbnails->set($video->getId(), $this->courseTransformer->thumbnailData($course));
        }

        return $video->getId();
        /*
        */
    }

    public function uploadFormation(int $formationId, array $accessToken): string
    {
        $formation = $this->em->getRepository(Formation::class)->find($formationId);
        if (!$formation) {
            throw new \RuntimeException("Playlist #{$formationId} introuvable");
        }

        $this->googleClient->setAccessToken($accessToken);
        $youtube = new \Google_Service_YouTube($this->googleClient);

        // Création ou mise à jour de la playlist YouTube
        $playlist = $this->formationTransformer->transform($formation);
        $youtubePlaylistId = $formation->getYoutubePlaylist();

        if ($youtubePlaylistId) {
            $playlist = $youtube->playlists->update('snippet,status', $playlist);
        } else {
            $playlist = $youtube->playlists->insert('snippet,status', $playlist);
            $formation->setYoutubePlaylist($playlist->getId());
            $this->em->flush();
            $youtubePlaylistId = $playlist->getId();
        }

        // === 1. Liste des vidéos de la formation ===
        $courseYoutubeIds = [];
        foreach ($formation->getChapters() as $chapter) {
            foreach ($chapter->getModules() as $course) {
                if ($course->getYoutubeId()) {
                    $courseYoutubeIds[] = $course->getYoutubeId();
                }
            }
        }

        // === 2. Liste actuelle des vidéos dans la playlist ===
        $playlistItems = $this->fetchPlaylistItems($youtube, $youtubePlaylistId);
        $existingIds = array_column($playlistItems, 'videoId');

        // === 3. Suppression des vidéos qui ne sont plus dans la formation ===
        foreach ($playlistItems as $item) {
            if (!in_array($item['videoId'], $courseYoutubeIds)) {
                $youtube->playlistItems->delete($item['itemId']);
            }
        }

        // === 4. Ajout des vidéos manquantes ===
        foreach ($courseYoutubeIds as $videoId) {
            if (in_array($videoId, $existingIds)) {
                continue; // déjà présent
            }

            $resourceId = new \Google_Service_YouTube_ResourceId();
            $resourceId->setKind('youtube#video');
            $resourceId->setVideoId($videoId);

            $snippet = new \Google_Service_YouTube_PlaylistItemSnippet();
            $snippet->setPlaylistId($youtubePlaylistId);
            $snippet->setResourceId($resourceId);

            $playlistItem = new \Google_Service_YouTube_PlaylistItem();
            $playlistItem->setSnippet($snippet);

            $youtube->playlistItems->insert('snippet', $playlistItem);
        }

        return $playlist->getId();
    }

    private function fetchPlaylistItems(\Google_Service_YouTube $youtube, string $playlistId): array
    {
        $items = [];
        $pageToken = null;

        do {
            $response = $youtube->playlistItems->listPlaylistItems('snippet', [
                'playlistId' => $playlistId,
                'maxResults' => 50,
                'pageToken' => $pageToken,
            ]);

            foreach ($response->getItems() as $item) {
                $videoId = $item->getSnippet()->getResourceId()->getVideoId();
                $items[] = [
                    'videoId' => $videoId,
                    'itemId' => $item->getId(), // nécessaire pour delete
                ];
            }

            $pageToken = $response->getNextPageToken();
        } while ($pageToken);

        return $items;
    }


    /**
     * Récupère les IDs des vidéos déjà présentes dans une playlist.
     */
    private function fetchPlaylistVideoIds(\Google_Service_YouTube $youtube, string $playlistId): array
    {
        $existingIds = [];
        $pageToken = null;

        do {
            $response = $youtube->playlistItems->listPlaylistItems('snippet', [
                'playlistId' => $playlistId,
                'maxResults' => 50,
                'pageToken' => $pageToken,
            ]);

            foreach ($response->getItems() as $item) {
                $existingIds[] = $item->getSnippet()->getResourceId()->getVideoId();
            }

            $pageToken = $response->getNextPageToken();
        } while ($pageToken);

        return $existingIds;
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
}