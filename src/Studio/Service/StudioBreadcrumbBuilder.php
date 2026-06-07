<?php

declare(strict_types=1);

namespace App\Studio\Service;

final class StudioBreadcrumbBuilder
{
    /**
     * @return list<array{label: string, route: ?string, icon: ?string}>
     */
    public function build(?string $routeName): array
    {
        if ($routeName === null) {
            return [];
        }

        if (!str_starts_with($routeName, 'studio_') && $routeName !== 'app_design_system') {
            return [];
        }

        return match ($routeName) {
            'studio_home' => [
                $this->crumb('Dashboard', null, 'layout-dashboard'),
            ],
            'studio_video_index' => [
                $this->crumb('Dashboard', 'studio_home', 'layout-dashboard'),
                $this->crumb('Vidéos', null, 'video'),
            ],
            'studio_video_new' => [
                $this->crumb('Dashboard', 'studio_home', 'layout-dashboard'),
                $this->crumb('Vidéos', 'studio_video_index', 'video'),
                $this->crumb('Nouvelle vidéo', null, 'plus'),
            ],
            'studio_article_index' => [
                $this->crumb('Dashboard', 'studio_home', 'layout-dashboard'),
                $this->crumb('Articles', null, 'file-text'),
            ],
            'studio_playlist_index' => [
                $this->crumb('Dashboard', 'studio_home', 'layout-dashboard'),
                $this->crumb('Playlists', null, 'list-video'),
            ],
            'app_design_system' => [
                $this->crumb('Dashboard', 'studio_home', 'layout-dashboard'),
                $this->crumb('Design system', null, 'palette'),
            ],
            default => [
                $this->crumb('Dashboard', 'studio_home', 'layout-dashboard'),
            ],
        };
    }

    /**
     * @return array{label: string, route: ?string, icon: ?string}
     */
    private function crumb(string $label, ?string $route, ?string $icon): array
    {
        return [
            'label' => $label,
            'route' => $route,
            'icon' => $icon,
        ];
    }
}
