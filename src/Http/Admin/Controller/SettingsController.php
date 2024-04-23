<?php

namespace App\Http\Admin\Controller;

use App\Domain\Application\Entity\Option;
use App\Domain\Application\Form\OpenDaysForm;
use App\Domain\Application\Service\OptionService;
use App\Http\Controller\AbstractController;
use App\Http\Type\ChoiceMultipleType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/parametres', name: 'settings_' )]
#[IsGranted( 'ROLE_ADMIN' )]
class SettingsController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly OptionService          $optionService
    )
    {
    }

    #[Route( '/', name: 'index' )]
    public function settings( Request $request ) : Response
    {
        $options = $this->optionService->findAll();
        dd( $options );

        return $this->render( 'admin/settings/index.html.twig', [
            'form' => $form->createView(),
        ] );
    }

}