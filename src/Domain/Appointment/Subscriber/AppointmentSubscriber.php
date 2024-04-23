<?php

namespace App\Domain\Appointment\Subscriber;

use App\Domain\Appointment\Entity\Appointment;
use App\Domain\Appointment\Event\AppointmentPaymentSuccessEvent;
use App\Domain\Appointment\Event\CanceledAppointmentEvent;
use App\Domain\Appointment\Event\ConfirmedAppointmentEvent;
use App\Domain\Appointment\Event\CreatedAppointmentEvent;
use App\Domain\Appointment\Event\UpdatedAppointmentEvent;
use App\Infrastructure\AppConfigService;
use App\Infrastructure\Mailing\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AppointmentSubscriber implements EventSubscriberInterface
{

    public function __construct(
        private readonly MailService            $mailService,
        private readonly UrlGeneratorInterface  $urlGenerator,
        private readonly AppConfigService       $appConfigService,
        private readonly EntityManagerInterface $em,
    )
    {
    }

    public static function getSubscribedEvents() : array
    {
        return [
            CreatedAppointmentEvent::class => 'onNewAppointment',
            UpdatedAppointmentEvent::class => 'onUpdateAppointment',
            ConfirmedAppointmentEvent::class => 'onConfirmAppointment',
            CanceledAppointmentEvent::class => 'onCanceledAppointment',
            AppointmentPaymentSuccessEvent::class => 'onAppointmentPaymentSuccess',
        ];
    }

    public function onAppointmentPaymentSuccess( AppointmentPaymentSuccessEvent $event ) : void
    {
        $appointments = $event->getAppointments();
        // set appointment status to confirmed
        foreach ( $appointments as $appointment ) {
            $appointment->setIsPaid( true );
            $this->em->persist( $appointment );
        }
        $this->em->flush();
    }

    public function onNewAppointment( CreatedAppointmentEvent $event ) : void
    {

        $client = $event->getAppointment()->getClient();
        $appointment = $event->getAppointment();

        $appointmentUrlClient = $this->urlGenerator->generate( 'app_appointment_manage', ['token' => $appointment->getAccessToken()], UrlGeneratorInterface::ABSOLUTE_URL );
        $appointmentUrlAdmin = $this->urlGenerator->generate( 'app_admin_appointment_show', ['id' => $event->getAppointment()->getId()], UrlGeneratorInterface::ABSOLUTE_URL );


        $email = $this->mailService->createEmail( 'mails/appointment/new-appointment-request.twig', [
            'appointment' => $appointment,
            'appointment_url' => $appointmentUrlClient,
        ] )
            ->to( $client->getEmail() )
            ->subject( 'Nouveau rendez-vous' );

        $this->mailService->send( $email );

        // Admin notification
        $email = $this->mailService->createEmail( 'mails/admin/appointment/new-appointment-request.twig', [
            'appointment' => $appointment,
            'appointment_url' => $appointmentUrlAdmin,
        ] )
            ->to( $this->appConfigService->getAdminEmail() )
            ->subject( 'Nouvelle réservation' );

        $this->mailService->send( $email );
    }

    public function onUpdateAppointment( UpdatedAppointmentEvent $event ) : void
    {
        $client = $event->getAppointment()->getClient();
        $appointment = $event->getAppointment();

        $appointmentUrlAdmin = $this->urlGenerator->generate( 'app_admin_appointment_show', ['id' => $appointment->getId()], UrlGeneratorInterface::ABSOLUTE_URL );
        $appointmentUrlClient = $this->urlGenerator->generate( 'app_appointment_manage', ['token' => $appointment->getAccessToken()], UrlGeneratorInterface::ABSOLUTE_URL );

        // Envoi de l'email de confirmation au client
        $email = $this->mailService->createEmail( 'mails/appointment/update-appointment.twig', [
            'appointment' => $appointment,
            'appointment_url' => $appointmentUrlClient,
        ] )
            ->to( $client->getEmail() )
            ->subject( 'Votre réservation a été modifiée' );

        $this->mailService->send( $email );

        // Envoi de l'email de notification à l'admin
        $email = $this->mailService->createEmail( 'mails/admin/appointment/update-appointment.twig', [
            'appointment' => $appointment,
            'appointment_url' => $appointmentUrlAdmin,
        ] )
            ->to( $this->appConfigService->getAdminEmail() )
            ->subject( 'Modification d\'une réservation' );

        $this->mailService->send( $email );
    }

    public function onConfirmAppointment( ConfirmedAppointmentEvent $event ) : void
    {
        $client = $event->getAppointment()->getClient();
        $appointment = $event->getAppointment();

        $appointmentUrlAdmin = $this->urlGenerator->generate( 'app_admin_appointment_show', ['id' => $appointment->getId()], UrlGeneratorInterface::ABSOLUTE_URL );
        $appointmentUrlClient = $this->urlGenerator->generate( 'app_appointment_manage', ['token' => $appointment->getAccessToken()], UrlGeneratorInterface::ABSOLUTE_URL );

        // Client notification
        $email = $this->mailService->createEmail( 'mails/appointment/confirm-appointment.twig', [
            'appointment' => $appointment,
            'appointment_url' => $appointmentUrlClient,
        ] )
            ->to( $client->getEmail() )
            ->subject( 'Votre réservation a été confirmée' );

        $this->mailService->send( $email );

        // Admin notification
        $email = $this->mailService->createEmail( 'mails/admin/appointment/confirm-appointment.twig', [
            'appointment' => $appointment,
            'appointment_url' => $appointmentUrlAdmin,
        ] )
            ->to( $this->appConfigService->getAdminEmail() )
            ->subject( 'Confirmation d\'une réservation' );

        $this->mailService->send( $email );
    }

    public function onCanceledAppointment( CanceledAppointmentEvent $event ) : void
    {
        $client = $event->getAppointment()->getClient();
        $appointment = $event->getAppointment();

        $appointmentUrlAdmin = $this->urlGenerator->generate( 'app_admin_appointment_show', ['id' => $appointment->getId()], UrlGeneratorInterface::ABSOLUTE_URL );
        $appointmentUrlClient = $this->urlGenerator->generate( 'app_appointment_manage', ['token' => $appointment->getAccessToken()], UrlGeneratorInterface::ABSOLUTE_URL );

        // Client notification
        $email = $this->mailService->createEmail( 'mails/appointment/cancel-appointment.twig', [
            'appointment' => $appointment,
            'appointment_url' => $appointmentUrlClient,
            'contact_url' => $this->urlGenerator->generate( 'app_contact', [], UrlGeneratorInterface::ABSOLUTE_URL )
        ] )
            ->to( $client->getEmail() )
            ->subject( 'Annulation de votre rendez-vous' );

        $this->mailService->send( $email );

        // Admin notification
        $email = $this->mailService->createEmail( 'mails/admin/appointment/cancel-appointment.twig', [
            'appointment' => $appointment,
            'appointment_url' => $appointmentUrlAdmin,
        ] )
            ->to( $this->appConfigService->getAdminEmail() )
            ->subject( 'Confirmation d\'annulation du rendez-vous' );

        $this->mailService->send( $email );

    }
}