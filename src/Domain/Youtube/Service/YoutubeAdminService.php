<?php

namespace App\Domain\Youtube\Service;

use App\Domain\Youtube\Entity\YoutubeSetting;
use App\Domain\Youtube\Repository\YoutubeSettingRepository;

class YoutubeAdminService
{
    public function __construct(
        private readonly YoutubeSettingRepository $youtubeSettingRepository,
    ){}

    public function getAccount() : YoutubeSetting|null
    {
        $settings = $this->youtubeSettingRepository->findOneBy([]);

        if (!$settings) {
            $settings = new YoutubeSetting();
            $this->youtubeSettingRepository->save($settings, true);
        }

        return $settings;
    }
}