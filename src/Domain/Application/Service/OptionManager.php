<?php

namespace App\Domain\Application\Service;

use App\Domain\Application\Entity\Option;
use App\Domain\Application\Repository\OptionRepository;
use App\Helper\OptionManagerInterface;
use App\Http\Type\ChoiceMultipleType;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class OptionManager implements OptionManagerInterface
{
    public function __construct(
        private readonly OptionRepository       $optionRepository,
    )
    {
    }

    public function get( string $key, ?string $default = null ) : ?string
    {
        $option = $this->optionRepository->find( $key );

        return null === $option ? $default : $option->getValue();
    }

    public function set( string $key, string $value ) : void
    {
        $option = $this->optionRepository->find( $key );
        if (null === $option) {
            $option = (new Option())
                ->setKey($key)
                ->setValue($value);
        } else {
            $option->setValue($value);
        }
        $this->optionRepository->save($option, true);
    }

    public function delete( string $key ) : void
    {
        $option = $this->optionRepository->find( $key );
        if (null === $option) {
            return;
        }
        $this->optionRepository->remove($option, true);
    }

    public function all(?array $keys = null): array
    {
        // Récupération des options selon la condition
        $options = (null === $keys)
            ? $this->optionRepository->findAll()
            : $this->optionRepository->findBy(['key' => $keys]);

        // Transformer les options en tableau clé-valeur
        $optionsByKey = array_reduce($options, function (array $acc, Option $option) {
            $acc[$option->getKey()] = $option->getValue();
            return $acc;
        }, []);

        // Si aucune clé spécifique n'est demandée, on renvoie tout
        if (null === $keys) {
            return $optionsByKey;
        }

        // Retourne uniquement les clés demandées (avec valeur null si absentes)
        return array_merge(array_fill_keys($keys, null), $optionsByKey);
    }

}