<?php

declare( strict_types=1 );

namespace App\Http\Controller;

use App\Http\Requirements;
use App\Infrastructure\Image\SymfonyResponseFactory;
use League\Glide\ServerFactory;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Attribute\Route;

class ImageController extends AbstractController
{
    private readonly string $cachePath;
    private readonly string $resizeKey;
    private readonly string $publicPath;

    public function __construct(ParameterBagInterface $parameterBag) {
        $projectDir = $parameterBag->get('kernel.project_dir');
        $resizeKey = $parameterBag->get('image_resize_key');
        if (!is_string($projectDir)) {
            throw new \RuntimeException('Parameter kernel.project_dir must be string');
        }

        if (!is_string($resizeKey)) {
            throw new \RuntimeException('Parameter "image_resize_key" must be string');
        }

        $this->cachePath = $projectDir . '/var/images';
        $this->publicPath = $projectDir . '/public';
        $this->resizeKey = $resizeKey;
    }

    #[Route( '/media/resize/{width}/{height}/{path}', name: 'image_resize', requirements: ['width' => Requirements::ID, 'height' => Requirements::ID, 'path' => Requirements::ANY] )]
    public function image( int $width, int $height , string $path, Request $request ) : Response
    {
        $server = ServerFactory::create([
            'source' => $this->publicPath,
            'cache' => $this->cachePath,
            'driver' => 'imagick',
            'response' => new SymfonyResponseFactory(),
            'defaults' => [
                'q' => 100,
                'fm' => 'jpeg',
                'fit' => 'crop',
            ],
        ]);
        [$url] = explode('?', $request->getRequestUri());
        try {
            SignatureFactory::create($this->resizeKey)->validateRequest($url, ['s' => $request->get('s')]);

            return $server->getImageResponse($path, ['w' => $width, 'h' => $height, 'fit' => 'crop']);
        } catch ( SignatureException $e ) {
            throw new HttpException(403, 'Signature invalide');
        }
    }

    #[Route( path: '/media/convert/{path}', name: 'image_jpg', requirements: ['path' => '.+'] )]
    public function convert(string $path, Request $request): Response
    {
        $server = ServerFactory::create([
            'source' => $this->publicPath,
            'cache' => $this->cachePath,
            'driver' => 'imagick',
            'response' => new SymfonyResponseFactory(),
            'defaults' => [
                'q' => 100,
                'fm' => 'jpg',
                'fit' => 'crop',
            ],
        ]);
        [$url] = explode('?', $request->getRequestUri());
        try {
            SignatureFactory::create($this->resizeKey)->validateRequest($url, ['s' => $request->get('s')]);

            return $server->getImageResponse($path, ['fm' => 'jpg']);
        } catch (SignatureException) {
            throw new HttpException(403, 'Signature invalide');
        }
    }
}
