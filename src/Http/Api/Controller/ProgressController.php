<?php

namespace App\Http\Api\Controller;

use App\Domain\Application\Entity\Content;
use App\Domain\Auth\Core\Entity\User;
use App\Domain\Course\Entity\Course;
use App\Domain\Course\Entity\Formation;
use App\Domain\Course\Entity\Technology;
use App\Domain\History\Entity\Progress;
use App\Domain\History\Event\ProgressEvent;
use App\Domain\History\Exception\AlreadyFinishedException;
use App\Http\Controller\AbstractController;
use App\Http\Security\ContentVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;


class ProgressController extends AbstractController
{
    public function __construct(
        private readonly EventDispatcherInterface $dispatcher,
        private readonly EntityManagerInterface $em,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly SerializerInterface $serializer,
    )
    {
    }

    #[Route( path: '/progress/{content<\d+>}/{progress}', name: 'progress', requirements: ['progress' => '^([1-9][0-9]{0,2}|1000)$'], methods: ['POST'] )]
    #[IsGranted(ContentVoter::PROGRESS, subject: 'content')]
    public function progress(
        Content $content,
        int $progress
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();
        try {
            $this->dispatcher->dispatch(new ProgressEvent($content, $user, $progress / Progress::TOTAL));
        } catch (AlreadyFinishedException) {
            return new JsonResponse([
                'title' => 'Vous avez déjà terminé ce cours',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        $this->em->flush();

        if (Progress::TOTAL !== $progress) {
            return new JsonResponse([]);
        }

        // return $this->serializer->serialize($path, 'path', ['url' => false]);

        $button = null;
        if ($content instanceof Course && $content->getFormation() instanceof Formation) {
            /** @var Formation $formation */
            $formation = $content->getFormation();
            $nextChapterId = $formation->getNextCourseId($content->getId());
            if ($nextChapterId) {
                $path = $this->em->getRepository(Course::class)->find($nextChapterId);
                $button = [
                    'title' => 'Voir le vidéo suivante',
                    'anchor' => 'autoplay',
                    'target' => $this->serializer->serialize($path, 'path', ['url' => false])
                ];
            } else {
                $button = [
                    'title' => 'Voir d\'autres playlists',
                    'target' => $this->urlGenerator->generate('app_formation_index', [], UrlGeneratorInterface::ABSOLUTE_URL),
                ];
            }
        } elseif ($content instanceof Course) {
            $technologies = $content->getMainTechnologies();
            if (count($technologies) > 0) {
                $technologie = $this->em->getRepository(Technology::class)->find($technologies[0]->getId());
                $button = [
                    'title' => "Voir d'autres vidéos {$technologie->getName()}",
                    'anchor' => 'tutoriels',
                    'target' => $this->urlGenerator->generate( 'app_technology_show', ['slug' => $technologie->getSlug()], UrlGeneratorInterface::ABSOLUTE_URL ),
                ];
            }
        }

        return new JsonResponse([
            'message' => $this->renderView('pages/public/courses/_success.html.twig', [
                'button' => $button,
            ]),
        ]);
    }

    #[Route(path: '/progress/{id<\d+>}', name: 'delete_progress', methods: ['DELETE'])]
    #[IsGranted('DELETE_PROGRESS', subject: 'progress')]
    public function deleteProgress(Progress $progress): JsonResponse
    {
        $this->em->remove($progress);
        $this->em->flush();

        return new JsonResponse([]);
    }
}