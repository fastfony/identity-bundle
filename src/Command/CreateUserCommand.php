<?php

declare(strict_types=1);

namespace Fastfony\IdentityBundle\Command;

use Fastfony\IdentityBundle\Manager\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'fastfony:user:create',
    description: 'Create a new user in the database.',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserManager $userManager,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $io = new SymfonyStyle($input, $output);

        $email = $this->askEmail($io);

        if (null !== $this->userManager->findByEmail($email)) {
            $io->error(sprintf('A user with the email "%s" already exists.', $email));

            return Command::FAILURE;
        }

        $password = $this->askPassword($io);

        $user = $this->userManager->create(
            $email,
            $password,
        );
        $this->userManager->enable($user);
        $this->userManager->save($user);

        $io->success(sprintf('User "%s" successfully created.', $email));

        return Command::SUCCESS;
    }

    private function askEmail(SymfonyStyle $io): string
    {
        $email = $io->ask('Email address');

        if (null === $email || '' === $email) {
            $io->error('Email cannot be empty.');

            return $this->askEmail($io);
        }

        return (string) $email;
    }

    private function askPassword(SymfonyStyle $io): string
    {
        $password = $io->askHidden('Password');

        if (null === $password || '' === $password) {
            $io->error('Password cannot be empty.');

            return $this->askPassword($io);
        }

        return (string) $password;
    }
}

