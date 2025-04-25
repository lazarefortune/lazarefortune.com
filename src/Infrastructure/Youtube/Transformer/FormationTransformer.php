<?php

namespace App\Infrastructure\Youtube\Transformer;

use App\Domain\Course\Entity\Formation;
use App\Domain\Course\Entity\Course;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Transforme une Formation en objet / tableau adapté à l'API Youtube (playlist).
 */
class FormationTransformer
{
    public function __construct(
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * Transforme une Formation en playlist YouTube.
     */
    public function transform(Formation $formation): \Google_Service_YouTube_Playlist
    {
        $youtubePlaylistId = $formation->getYoutubePlaylist();
        $title = preg_replace('/[<>]/', '', $formation->getTitle() ?: '');

        // On crée l'objet Playlist
        $playlist = new \Google_Service_YouTube_Playlist();

        $snippet = new \Google_Service_YouTube_PlaylistSnippet();
        $snippet->setTitle($title);
        $snippet->setDescription($this->buildDescription($formation));
        $snippet->setDefaultLanguage('fr');

        $status = new \Google_Service_YouTube_PlaylistStatus();
        if ($formation->isOnline()) {
            $formationStatus = 'public';
        } else {
            $formationStatus = 'private';
        }
        $status->setPrivacyStatus($formationStatus);

        $playlist->setSnippet($snippet);
        $playlist->setStatus($status);

        if ($youtubePlaylistId) {
            $playlist->setId($youtubePlaylistId);
        }

        return $playlist;
    }

    /**
     * Construit une description adaptée pour YouTube.
     */
    public function buildDescription(Formation $formation): string
    {
        $title = $formation->getTitle();
        $short = $formation->getShort();
        $url = 'https://lazarefortune.com';

        $hashtags = [
            '#Formation',
            '#LazareFortune',
            '#LazareFortuneCode',
            '#DevWeb',
            '#Learning'
        ];

        $hashtagText = implode(' ', $hashtags);

        return <<<DESC
$title

$short

Découvre toutes mes ressources ici : $url  
Retrouve-moi sur les réseaux avec **@lazarefortune**

🔗 Explore, apprends et partage si ça t’aide !

$hashtagText
DESC;
    }
}
