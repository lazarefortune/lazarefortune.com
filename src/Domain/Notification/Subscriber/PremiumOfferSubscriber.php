<?php

namespace App\Domain\Notification\Subscriber;

use App\Domain\Premium\Event\PremiumOfferReceived;
use App\Domain\Premium\PremiumService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PremiumOfferSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly PremiumService $premiumService,
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            PremiumOfferReceived::class => 'onPremiumOfferReceived',
        ];
    }

    public function onPremiumOfferReceived( PremiumOfferReceived $event ) : void
    {
        $offer = $event->getPremiumOffer();
        $user = $event->getUser();

        $this->premiumService->notifyPremiumOfferNotification( $user, $offer );
    }
}