<?php

namespace App\Domain\Newsletter\Service;

use App\Domain\Auth\Core\Entity\User;
use App\Domain\Auth\Core\Repository\UserRepository;
use App\Domain\Auth\Registration\Service\RegistrationService;
use App\Domain\Course\Repository\CourseRepository;
use App\Domain\Newsletter\Entity\NewsletterSubscriber;
use App\Domain\Newsletter\Exception\AlreadySubscribedException;
use App\Domain\Newsletter\Repository\NewsletterRepository;
use App\Infrastructure\Mailing\MailService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

class NewsletterService
{
    private const BASE_URL = 'https://www.lazarefortune.com';

    public function __construct(
        private readonly MailService            $mailService,
        private readonly CourseRepository       $courseRepository,
        private readonly UserRepository         $userRepository,
        private readonly UrlGeneratorInterface  $urlGenerator,
        private readonly NewsletterRepository   $newsletterRepository,
        private readonly EntityManagerInterface $em
    )
    {
    }

    /**
     * @throws Exception
     */
    public function subscribe( NewsletterSubscriber $subscriber ) : void
    {
        $existingSubscriber = $this->em->getRepository( NewsletterSubscriber::class )
            ->findOneBy( ['email' => $subscriber->getEmail()] );

        $existingUser = $this->userRepository->findOneBy( ['email' => $subscriber->getEmail()] );

        if ( $existingSubscriber ) {
            if ( $existingSubscriber->isSubscribed() ) {
                throw new AlreadySubscribedException();
            }
            $existingSubscriber->setSubscribed( true );
            $this->em->flush();
        } elseif ( $existingUser ) {
            /*
            $existingSubscriber = new NewsletterSubscriber();
            $existingSubscriber->setEmail($subscriber->getEmail());
            $existingSubscriber->setSubscribed(true);
            $this->em->persist($existingSubscriber);
            */
            $existingUser->setNewsletterSubscribed( true );
            $this->em->persist( $existingUser );
            $this->em->flush();

            if ( !$existingUser->isNewsletterSubscribed() ) {
                $existingUser->setNewsletterSubscribed( true );
                $this->em->flush();
            }
        } else {
            $this->em->persist( $subscriber );
            $this->em->flush();
        }
    }




//
//    public function sendNewsletters(): void
//    {
//        $todayCourses = $this->courseRepository->getTodayCourses();
//
//        if (empty($todayCourses)) {
//            return; // Pas de cours à envoyer
//        }
//
//        $coursesWithUrls = $this->addUrlsToCourses($todayCourses);
//
//        $users = $this->userRepository->findAll();
//
//        foreach ($users as $user) {
//            if (!$this->isValidUser($user)) {
//                continue;
//            }
//            $this->sendNewsletterToUser($user, $coursesWithUrls);
//        }
//    }
//
//    private function addUrlsToCourses(array $courses): array
//    {
//        return array_map(function (array $course): array {
//            $course['url'] = $this->urlGenerator->generate(
//                'app_course_show',
//                ['slug' => $course['slug']],
//                UrlGeneratorInterface::ABSOLUTE_PATH
//            );
//            $course['url'] = self::BASE_URL . $course['url'];
//            return $course;
//        }, $courses);
//    }
//
//    private function isValidUser(User $user): bool
//    {
//        return $user->getEmail() !== null && filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)
//            && $user->isVerified();
//    }
//
//    private function sendNewsletterToUser($user, array $courses): void
//    {
//        $email = $this->mailService->createEmail('mails/newsletters/daily_newsletter_videos.twig', [
//            'courses' => $courses,
//            'user' => $user,
//            'courses_url' => self::BASE_URL . $this->urlGenerator->generate('app_course_index', [], UrlGeneratorInterface::ABSOLUTE_PATH),
//        ]);
//
//        $email->to($user->getEmail())
//            ->subject('Découvrez les nouvelles vidéos du jour !');
//
//        $this->mailService->send($email);
//    }
}
