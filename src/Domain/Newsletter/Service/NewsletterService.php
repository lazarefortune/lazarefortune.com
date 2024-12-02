<?php

namespace App\Domain\Newsletter\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Course\Repository\CourseRepository;
use App\Infrastructure\Mailing\MailService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class NewsletterService
{
    public function __construct(
        private readonly MailService $mailService,
        private readonly CourseRepository $courseRepository,
        private readonly UserRepository $userRepository,
        private readonly UrlGeneratorInterface $urlGenerator
    ) {}

    public function sendNewsletters(): void
    {
        $todayCourses = $this->courseRepository->getTodayCourses();

        if (empty($todayCourses)) {
            return; // Pas de cours à envoyer
        }

        $coursesWithUrls = $this->addUrlsToCourses($todayCourses);

        $users = $this->userRepository->findAll();

        foreach ($users as $user) {
            if (!$this->isValidUser($user)) {
                continue;
            }
            $this->sendNewsletterToUser($user, $coursesWithUrls);
        }
    }

    private function addUrlsToCourses(array $courses): array
    {
        return array_map(function (array $course): array {
            $course['url'] = $this->urlGenerator->generate(
                'app_course_show',
                ['slug' => $course['slug']],
                UrlGeneratorInterface::ABSOLUTE_URL
            );
            return $course;
        }, $courses);
    }

    private function isValidUser(User $user): bool
    {
        return $user->getEmail() !== null && filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)
            && $user->isVerified();
    }

    private function sendNewsletterToUser($user, array $courses): void
    {
        $email = $this->mailService->createEmail('mails/newsletters/daily_newsletter_videos.twig', [
            'courses' => $courses,
            'user' => $user,
            'courses_url' => $this->urlGenerator->generate(
                'app_course_index',
                [],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
        ]);

        $email->to($user->getEmail())
            ->subject('Découvrez les nouvelles vidéos du jour !');

        $this->mailService->send($email);
    }
}
