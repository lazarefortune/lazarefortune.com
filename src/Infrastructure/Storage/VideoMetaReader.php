<?php

namespace App\Infrastructure\Storage;

use getID3;

class VideoMetaReader
{
    public function getDuration(string $videoPath): ?int {
        $getID3 = new getID3();
        $fileInfo = $getID3->analyze($videoPath);

        $errors = ( $fileInfo['error'] ?? array() );

        if (!empty($errors)) {
            return 0;
        }

        if (isset($fileInfo['playtime_seconds'])) {
            return (int) round($fileInfo['playtime_seconds']);
        }
        return 0;
    }
}