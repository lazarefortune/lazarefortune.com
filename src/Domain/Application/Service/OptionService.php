<?php

namespace App\Domain\Application\Service;

use App\Domain\Application\Entity\Option;
use App\Domain\Application\Repository\OptionRepository;
use App\Http\Type\ChoiceMultipleType;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class OptionService
{
    public function __construct(
        private readonly OptionRepository       $optionRepository,
        private readonly CacheItemPoolInterface $cache
    )
    {
    }

    /**
     * @return array<Option>
     */
    public function getAll() : array
    {
        return $this->optionRepository->findAll();
    }

    public function getValue( string $name ) : mixed
    {
        $option = $this->optionRepository->findOneBy( ['name' => $name] );

        if ( $option instanceof Option ) {
            return $option->getValue();
        }

        return null;
    }

    /**
     * @return array<Option>
     */
    public function findAll() : array
    {
        return $this->optionRepository->findAllForTwig();
    }

    /**
     * @throws InvalidArgumentException
     */
    public function getApplicationName() : string
    {
        $cacheItem = $this->cache->getItem( 'app_name' );

        if ( !$cacheItem->isHit() ) {
            $appName = $this->getValue( 'site_title' );
            $cacheItem->set( $appName );
            $cacheItem->expiresAfter( 86400 );  // Set cache TTL to 1 day
            $this->cache->save( $cacheItem );
        } else {
            $appName = $cacheItem->get();
        }

        return $appName;
    }

    private function createOption( string $label, string $name, mixed $value, string $type ) : Option
    {
        $option = new Option( $label, $name, $value, $type );
        $this->optionRepository->save( $option, true );

        return $option;
    }
}