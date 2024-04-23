<?php

namespace App\Domain\Client\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;

class EmailActionForm extends AbstractType
{
    public function buildForm( FormBuilderInterface $builder, array $options )
    {
        $builder
            ->add( 'action', ChoiceType::class, [
                'choices' => [
                    'Renvoyer la dernière facture' => 'last_invoice',
                    'Envoyer un rappel de paiement' => 'payment_reminder',
                    'Envoyer un rappel de rendez-vous' => 'appointment_reminder',
                    'Envoyer un email de réinitialisation de mot de passe' => 'password_reset',
                    'Envoyer un email de confirmation de compte' => 'account_confirmation',
                ],
                'label' => false,
            ] );
    }
}