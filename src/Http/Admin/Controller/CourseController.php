<?php

namespace App\Http\Admin\Controller;


use App\Domain\Course\Entity\Course;
use App\Http\Admin\Data\Crud\CourseCrudData;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Handler\UploadHandler;

#[IsGranted('ROLE_ADMIN')]
#[Route(path: '/tutoriels', name: 'course_')]
class CourseController extends CrudController
{
    protected string $templatePath = 'course';
    protected string $menuItem = 'course';
    protected string $entity = Course::class;
    protected bool $indexOnSave = false;
    protected string $routePrefix = 'app_admin_course';
    protected array $events = [];

    #[Route(path: '/', name: 'index')]
    public function index(Request $request): Response
    {
        $this->paginator->allowSort('row.id', 'row.online');
        $query = $this->getRepository()
            ->createQueryBuilder('row')
            ->addSelect('tu', 't')
            ->leftJoin('row.technologyUsages', 'tu')
            ->leftJoin('tu.technology', 't')
            ->orderBy('row.createdAt', 'DESC')
            ->setMaxResults(10)
        ;
        if ($request->query->has('technology')) {
            $query
                ->andWhere('t.slug = :technology')
                ->setParameter('technology', $request->query->get('technology'));
        }

        return $this->crudIndex($query);
    }

    #[Route(path: '/nouveau', name: 'new', methods: ['POST', 'GET'])]
    public function new(): Response
    {
        $entity = (new Course())->setAuthor($this->getUser());
        $data = new CourseCrudData($entity);

        return $this->crudNew($data);
    }

    #[Route(path: '/{id<\d+>}', name: 'edit', methods: ['POST', 'GET'])]
    public function edit(
        Request          $request,
        Course           $course,
        UploadHandler    $uploaderHelper,
    ): Response {
        $data = (new CourseCrudData($course, $uploaderHelper))->setEntityManager($this->em);
        $response = $this->crudEdit($data);
        if ($request->request->get('upload')) {
            dd($request->request->get('upload'));
//            $session->set(self::UPLOAD_SESSION_KEY, $course->getId());

//            return $this->redirectToRoute('admin_course_upload');
        }

        return $response;
    }

    #[Route(path: '/{id<\d+>}', methods: ['DELETE'])]
    public function delete(Course $course, EventDispatcherInterface $dispatcher): Response
    {
        $course->setOnline(false);
        $course->setUpdatedAt(new \DateTime());
        $this->em->flush();
        $this->addFlash('success', 'Le tutoriel a bien été mis hors ligne');

        if ($this->events['delete'] ?? null) {
            $dispatcher->dispatch(new $this->events['delete']($course));
        }

        return $this->redirectBack(($this->routePrefix.'_index'));
    }
}