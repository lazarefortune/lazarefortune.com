<?php

namespace App\Domain\Notification;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Notification\Entity\Notification;
use App\Domain\Notification\Event\NotificationCreatedEvent;
use App\Domain\Notification\Event\NotificationReadEvent;
use App\Domain\Notification\Repository\NotificationRepository;
use App\Http\Encoder\PathEncoder;
use App\Http\Security\ChannelVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Serializer\SerializerInterface;

class NotificationService
{
    public function __construct(
        private readonly SerializerInterface $serializer,
        private readonly EntityManagerInterface $em,
        private readonly EventDispatcherInterface $dispatcher,
        private readonly Security $security,
    ) {
    }

    /**
     * Envoie une notification sur un canal particulier.
     */
    public function notifyChannel(string $channel, string $message, ?object $entity = null): Notification
    {
        /** @var string $url */
        $url = $entity ? $this->serializer->serialize($entity, PathEncoder::FORMAT) : null;
        $notification = (new Notification())
            ->setMessage($message)
            ->setUrl($url)
            ->setTarget($entity ? $this->getHashForEntity($entity) : null)
            ->setCreatedAt(new \DateTimeImmutable())
            ->setChannel($channel);
        $this->em->persist($notification);
        $this->em->flush();
        $this->dispatcher->dispatch(new NotificationCreatedEvent($notification));

        return $notification;
    }

    /**
     * Envoie une notification à un utilisateur.
     */
    public function notifyUser(User $user, string $message, object $entity): Notification
    {
        /** @var string $url */
        $url = $this->serializer->serialize($entity, PathEncoder::FORMAT);
        /** @var NotificationRepository $repository */
        $repository = $this->em->getRepository(Notification::class);

        $notification = (new Notification())
            ->setMessage($message)
            ->setUrl($url)
            ->setTarget($this->getHashForEntity($entity))
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUser($user);
        $repository->persistOrUpdate($notification);
        $this->em->flush();
        $this->dispatcher->dispatch(new NotificationCreatedEvent($notification));

        return $notification;
    }

    /**
     * @return Notification[]
     */
    public function forUser(User $user, int $limit = 15): array
    {
        /** @var NotificationRepository $repository */
        $repository = $this->em->getRepository(Notification::class);

        return $repository->findRecentForUser($user, channels: $this->getChannelsForUser($user), limit: $limit);
    }

    public function readAll(User $user): void
    {
        $user->setNotificationsReadAt(new \DateTimeImmutable());
        $this->em->flush();
        $this->dispatcher->dispatch(new NotificationReadEvent($user));
    }

    /**
     * Renvoie les salons auquel l'utilisateur peut s'abonner.
     *
     * @return string[]
     */
    public function getChannelsForUser(User $user): array
    {
        $channels = [
            'user/'.$user->getId(),
            'public',
        ];

        if ($this->security->isGranted(ChannelVoter::LISTEN_ADMIN)) {
            $channels[] = 'admin';
        }

        return $channels;
    }

    /**
     * Extrait un hash pour une notification className::id.
     */
    private function getHashForEntity(object $entity): string
    {
        $hash = $entity::class;
        if (method_exists($entity, 'getId')) {
            $hash .= '::'.(string) $entity->getId();
        }

        return $hash;
    }
}