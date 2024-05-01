<?php

namespace App\Http\Admin\Controller;

use App\Domain\Application\Entity\Content;
use App\Domain\Attachment\Entity\Attachment;
use App\Domain\Attachment\Repository\AttachmentRepository;
use App\Http\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AttachmentController extends AbstractController
{
    public function __construct(private readonly ValidatorInterface $validator)
    {
    }

    public function validateRequest(Request $request) : array
    {
        $errors = $this->validator->validate($request->files->get('file'), [
            new Image(),
        ]);
        if (0 === $errors->count()) {
            return [true, null];
        }

        return [false, new JsonResponse(['error' => $errors->get(0)->getMessage()], 422)];
    }

    #[Route(path: '/attachment/folders', name: 'attachment_folders')]
    public function folders(AttachmentRepository $repository): JsonResponse
    {
        return new JsonResponse($repository->findYearsMonths());
    }

    #[Route(path: '/attachment/files', name: 'attachment_files')]
    public function files(AttachmentRepository $repository, Request $request): JsonResponse
    {
//        ['path' => $path, 'q' => $q] = $this->getFilterParams($request);
//        if ($q === 'orphan') {
//            $attachments = $repository->orphaned();
//        } elseif (!empty($q)) {
//            $attachments = $repository->search($q);
//        } elseif (null === $path) {
//            $attachments = $repository->findLatest();
//        } else {
//        }

        $folderQuery = $request->query->get('folder', '');
        if ($folderQuery !== '') {
            $attachments = $repository->findForPath($request->get('folder'));
        }else{
            $attachments = $repository->findLatest();
        }

        return $this->json($attachments);

    }

    #[Route(path: '/attachment/{attachment<\d+>?}', name: 'attachment_show', methods: ['POST'])]
    public function update(?Attachment $attachment, Request $request, EntityManagerInterface $em): JsonResponse
    {
        [$valid, $response] = $this->validateRequest($request);
        if (!$valid) {
            return $response;
        }
        $file = $request->files->get('file');
        if (!$file) {
            return new JsonResponse(['error' => 'No file provided'], 400);
        }
        if (null === $attachment) {
            $attachment = new Attachment();
        }
        $attachment->setFile($request->files->get('file'));
        $attachment->setCreatedAt(new \DateTime());
        $attachment->setFileSize($attachment->getFile()->getSize());
        $em->persist($attachment);
        $em->flush();

        return $this->json($attachment);
    }

    #[Route(path: '/attachment/files/{attachment<\d+>}', methods: ['DELETE'])]
    public function delete(Attachment $attachment, EntityManagerInterface $em): JsonResponse
    {
        // get content of the attachment
        /** @var Content $content */
        $content = $em->getRepository(Content::class)->findOneBy(['image' => $attachment]);
        $content?->setImage( null );
        $em->flush();

        // remove the attachment
        $em->remove($attachment);
        $em->flush();

        return $this->json([]);
    }

    private function getFilterParams(Request $request): array
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'path' => null,
            'q' => null,
        ]);
        $resolver->setAllowedTypes('path', ['string', 'null']);
        $resolver->setAllowedTypes('q', ['string', 'null']);
        $resolver->setAllowedValues('path', fn ($value) => null === $value || preg_match('/^2\d{3}\/(1[0-2]|0[1-9])$/', (string) $value) > 0);

        try {
            return $resolver->resolve($request->query->all());
        } catch (InvalidOptionsException $exception) {
            throw new HttpException(Response::HTTP_UNPROCESSABLE_ENTITY, $exception->getMessage());
        }
    }
}