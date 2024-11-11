<?php

namespace App\Http\Admin\Controller;

use App\Http\Controller\AbstractController;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class CacheController extends AbstractController
{
    #[Route(path: '/cache/clean', name: 'cache_clean', methods: ['POST'])]
    public function clean(CacheItemPoolInterface $cache): RedirectResponse
    {
        if ($cache->deleteItem('admin.youtube-subscribers-count')) {
            $this->addFlash('success', 'Le cache a bien été supprimé');
        } else {
            $this->addFlash('danger', "Le cache n'a pas pu être supprimé");
        }

        return $this->redirectToRoute('admin_home');
    }
}