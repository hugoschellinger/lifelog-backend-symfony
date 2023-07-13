<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:genere-new-random-string',
    description: 'Génère un nouveau APP_SECRET dans le .env',
)]
class GenereNewRandomStringCommand extends Command
{
    protected function configure(): void
    {
        $this
            ->addArgument('lengthString', InputArgument::REQUIRED, 'length of string')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $length = $input->getArgument('lengthString');

        $a = '0123456789abcdef';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $a[rand(0, 15)];
        }

        $io->success('New random 32-bits string was generated: ' . $secret);

        return 0;
    }
}
