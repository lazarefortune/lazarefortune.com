<?php

namespace App\Http\Admin\Controller;

use App\Domain\Auth\Entity\User;
use App\Domain\Auth\Event\UserRegistrationCompletedEvent;
use App\Domain\Client\Event\DeleteClientEvent;
use App\Domain\Client\Form\EmailActionForm;
use App\Domain\Client\Service\ClientService;
use App\Http\Admin\Data\Crud\ClientCrudData;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/clients', name: 'clients_' )]
#[IsGranted( 'ROLE_ADMIN' )]
class ClientsController extends CrudController
{

    protected string $templatePath = 'clients';
    protected string $menuItem = 'client';
    protected string $searchField = 'name';
    protected string $entity = User::class;
    protected string $routePrefix = 'app_admin_clients';
    protected array $events = [
        'create' => UserRegistrationCompletedEvent::class,
        'delete' => DeleteClientEvent::class
    ];

    #[Route( path: '/', name: 'index', methods: ['GET'] )]
    public function index() : Response
    {
        $queryBuilder = $this->getRepository()->createQueryBuilder( 'row' );
        $queryBuilder->where( 'row.roles LIKE :role' )
            ->setParameter( 'role', '%"ROLE_CLIENT"%' );

        // remove current user from list
        $user = $this->getUser();
        $queryBuilder->andWhere( 'row.id != :id' )
            ->setParameter( 'id', $user->getId() )
            ->orderBy( 'row.createdAt', 'DESC' )
            ->setMaxResults( 10 );

        return parent::crudIndex( $queryBuilder );
    }

    #[Route( path: '/new', name: 'new', methods: ['POST', 'GET'] )]
    public function new() : Response
    {
        $client = new User();
        $data = new ClientCrudData( $client );
        return $this->crudNew( $data );
    }

    #[Route( path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'] )]
    public function edit( User $client ) : Response
    {
        $data = new ClientCrudData( $client );
        return $this->crudEdit( $data );
    }

    #[Route( path: '/{id<\d+>}', methods: ['DELETE'] )]
    public function delete( User $client ) : Response
    {
        return $this->crudDelete( $client );
    }

    /**
     * @throws \Exception
     */
    #[Route( path: '/{id<\d+>}/show', name: 'show', methods: ['GET', 'POST'] )]
    public function show( Request $request, User $client, ClientService $clientService ) : Response
    {
        $client = $clientService->getClient( $client->getId() );
        $clientAppointments = $clientService->getClientAppointments( $client, 4 );

        $emailActionsForm = $this->createForm( EmailActionForm::class );
        $emailActionsForm->handleRequest( $request );

        if ( $emailActionsForm->isSubmitted() && $emailActionsForm->isValid() ) {
            try {
                $clientService->sendEmailAction( $client, $emailActionsForm->get( 'action' )->getData() );
            } catch ( \Exception $e ) {
                $this->addFlash( 'danger', $e->getMessage() );
                return $this->redirectToRoute( 'app_admin_clients_show', ['id' => $client->getId()] );
            }
            $this->addFlash( 'success', 'L\'email a bien été envoyé' );
            return $this->redirectToRoute( 'app_admin_clients_show', ['id' => $client->getId()] );
        }

        return $this->render( 'admin/clients/show.html.twig', [
            'client' => $client,
            'clientAppointments' => $clientAppointments,
            'emailActionsForm' => $emailActionsForm->createView(),
        ] );
    }

}
