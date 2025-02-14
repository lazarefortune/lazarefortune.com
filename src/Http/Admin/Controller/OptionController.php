<?php

namespace App\Http\Admin\Controller;

use App\Domain\Application\Entity\Option;
use App\Domain\Application\Form\EditOptionForm;
use App\Domain\Application\Form\OptionForm;
use App\Domain\Application\Service\OptionManager;
use App\Helper\OptionManagerInterface;
use App\Http\Controller\AbstractController;
use App\Infrastructure\Payment\Stripe\StripeApi;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route( '/option', name: 'option_' )]
#[IsGranted( 'ROLE_SUPER_ADMIN' )]
class OptionController extends AbstractController
{

    final public const MANAGEABLE_KEYS = ['spam_words','stripe_taxes_key'];

    public function __construct(
        private readonly OptionManagerInterface $optionManager,
        private readonly StripeApi $api
    )
    {}

    #[Route('/', name: 'index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->render('pages/admin/option/index.html.twig', [
           'options' => $this->optionManager->all(self::MANAGEABLE_KEYS)
        ]);
    }

    #[Route('/', name: 'update', methods: ['POST'])]
    public function update( Request $request ): JsonResponse
    {
        $data = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        $key = $data['key'] ?? null;
        $value = $data['value'] ?? null;
        if (!in_array($key, self::MANAGEABLE_KEYS, true)) {
            throw new UnprocessableEntityHttpException('Impossible de modifier cette clef');
        }
        $this->optionManager->set($key, $value);

        return $this->json([]);
    }

}
