<?php

declare(strict_types=1);

namespace App\Auth\Command;

use App\Auth\Entity\User;
use App\Auth\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:user:create-admin',
    description: 'Create a new admin user or promote an existing user to admin',
)]
final class CreateAdminUserCommand extends Command
{
    private const MIN_PASSWORD_LENGTH = 8;

    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly ValidatorInterface $validator,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Admin email address')
            ->addOption('password', null, InputOption::VALUE_REQUIRED, 'Password (required for new users in non-interactive mode)')
            ->addOption('fullname', null, InputOption::VALUE_REQUIRED, 'Full name')
            ->addOption('super-admin', null, InputOption::VALUE_NONE, 'Grant ROLE_SUPER_ADMIN in addition to ROLE_ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $email */
        $email = $input->getArgument('email');
        $email = mb_strtolower(trim($email));

        if (!$this->isValidEmail($email)) {
            $io->error(sprintf('Invalid email address: "%s".', $email));

            return Command::FAILURE;
        }

        $grantSuperAdmin = (bool) $input->getOption('super-admin');
        $user = $this->userRepository->findOneByEmail($email);
        $isNewUser = $user === null;

        if ($isNewUser) {
            $user = $this->createUser($input, $io, $email);
            if ($user === null) {
                return Command::FAILURE;
            }
        } else {
            if (!$this->updateExistingUser($input, $io, $user)) {
                return Command::FAILURE;
            }
        }

        $assignedRoles = $this->assignAdminRoles($user, $grantSuperAdmin);

        if ($isNewUser) {
            $this->entityManager->persist($user);
        }

        $this->entityManager->flush();

        if ($isNewUser) {
            $io->success(sprintf('Admin user "%s" created with roles: %s.', $email, implode(', ', $assignedRoles)));
        } else {
            $io->success(sprintf('User "%s" promoted with roles: %s.', $email, implode(', ', $assignedRoles)));
        }

        return Command::SUCCESS;
    }

    private function isValidEmail(string $email): bool
    {
        $violations = $this->validator->validate($email, [
            new Assert\NotBlank(),
            new Assert\Email(),
        ]);

        return $violations->count() === 0;
    }

    private function createUser(InputInterface $input, SymfonyStyle $io, string $email): ?User
    {
        $password = $input->getOption('password');

        if (!is_string($password) || $password === '') {
            if (!$input->isInteractive()) {
                $io->error('The --password option is required when creating a new user in non-interactive mode.');

                return null;
            }

            $password = $this->askPassword($input, $io);
            if ($password === null) {
                return null;
            }
        }

        if (!$this->isValidPassword($password)) {
            $io->error(sprintf('Password must be at least %d characters long.', self::MIN_PASSWORD_LENGTH));

            return null;
        }

        $user = (new User())
            ->setEmail($email)
            ->setIsVerified(true);

        $fullname = $input->getOption('fullname');
        if (is_string($fullname) && $fullname !== '') {
            $user->setFullname($fullname);
        }

        $user->setPassword($this->passwordHasher->hashPassword($user, $password));

        return $user;
    }

    private function updateExistingUser(InputInterface $input, SymfonyStyle $io, User $user): bool
    {
        $password = $input->getOption('password');

        if (is_string($password) && $password !== '') {
            if (!$this->isValidPassword($password)) {
                $io->error(sprintf('Password must be at least %d characters long.', self::MIN_PASSWORD_LENGTH));

                return false;
            }

            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
            $io->note('Password updated.');
        }

        $fullname = $input->getOption('fullname');
        if (is_string($fullname) && $fullname !== '') {
            $user->setFullname($fullname);
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function assignAdminRoles(User $user, bool $grantSuperAdmin): array
    {
        $roles = array_values(array_filter(
            $user->getRoles(),
            static fn (string $role): bool => $role !== User::ROLE_USER,
        ));

        if (!in_array(User::ROLE_ADMIN, $roles, true)) {
            $roles[] = User::ROLE_ADMIN;
        }

        if ($grantSuperAdmin && !in_array(User::ROLE_SUPER_ADMIN, $roles, true)) {
            $roles[] = User::ROLE_SUPER_ADMIN;
        }

        $user->setRoles($roles);

        return $user->getRoles();
    }

    private function askPassword(InputInterface $input, SymfonyStyle $io): ?string
    {
        $helper = $this->getHelper('question');

        $question = (new Question('Password (hidden): '))
            ->setHidden(true)
            ->setHiddenFallback(false)
            ->setValidator(function (?string $value): string {
                if (!is_string($value) || !$this->isValidPassword($value)) {
                    throw new \RuntimeException(sprintf('Password must be at least %d characters long.', self::MIN_PASSWORD_LENGTH));
                }

                return $value;
            });

        $password = $helper->ask($input, $io, $question);
        if (!is_string($password)) {
            $io->error('Password is required.');

            return null;
        }

        return $password;
    }

    private function isValidPassword(string $password): bool
    {
        return mb_strlen($password) >= self::MIN_PASSWORD_LENGTH;
    }
}
