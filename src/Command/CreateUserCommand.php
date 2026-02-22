<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:user:create',
    description: 'Crée un utilisateur avec l\'email et le mot de passe choisis.',
)]
class CreateUserCommand extends Command
{
    public function __construct(
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('email', null, InputOption::VALUE_OPTIONAL, 'Email de l\'utilisateur')
            ->addOption('password', null, InputOption::VALUE_OPTIONAL, 'Mot de passe en clair')
            ->addOption('firstname', null, InputOption::VALUE_OPTIONAL, 'Prénom', '')
            ->addOption('lastname', null, InputOption::VALUE_OPTIONAL, 'Nom', '')
            ->addOption('admin', null, InputOption::VALUE_NONE, 'Attribuer le rôle ROLE_ADMIN');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        /** @var QuestionHelper $helper */
        $helper = $this->getHelper('question');

        $email = $input->getOption('email');
        if ($email === null || $email === '') {
            $question = new Question('Email : ');
            $question->setValidator(function (?string $value): string {
                $value = trim((string) $value);
                if ($value === '') {
                    throw new \RuntimeException('L\'email ne peut pas être vide.');
                }
                if (!filter_var($value, \FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('Email invalide.');
                }
                return $value;
            });
            $email = $helper->ask($input, $output, $question);
        } else {
            $email = trim($email);
            if ($email === '') {
                $io->error('L\'email ne peut pas être vide.');
                return Command::FAILURE;
            }
            if (!filter_var($email, \FILTER_VALIDATE_EMAIL)) {
                $io->error('Email invalide.');
                return Command::FAILURE;
            }
        }

        $password = $input->getOption('password');
        if ($password === null || $password === '') {
            $question = new Question('Mot de passe : ');
            $question->setHidden(true);
            $question->setValidator(function (?string $value): string {
                if (trim((string) $value) === '') {
                    throw new \RuntimeException('Le mot de passe ne peut pas être vide.');
                }
                return $value;
            });
            $password = $helper->ask($input, $output, $question);
        } else {
            if (trim($password) === '') {
                $io->error('Le mot de passe ne peut pas être vide.');
                return Command::FAILURE;
            }
        }

        if ($this->userRepository->findOneBy(['email' => $email]) !== null) {
            $io->error(sprintf('Un utilisateur avec l\'email "%s" existe déjà.', $email));
            return Command::FAILURE;
        }

        $user = new User();
        $user->setEmail($email);
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setFirstname($input->getOption('firstname') ?: 'User');
        $user->setLastname($input->getOption('lastname') ?: '');

        if ($input->getOption('admin')) {
            $user->setRoles(['ROLE_ADMIN']);
        }

        $this->userRepository->save($user, true);

        $io->success(sprintf('Utilisateur créé : %s', $email));

        return Command::SUCCESS;
    }
}
