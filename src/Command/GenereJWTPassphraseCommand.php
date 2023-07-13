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
    name: 'app:genere-jwt-passphrase',
    description: 'Génère un nouveau secret pour JWT',
)]
class GenereJWTPassphraseCommand extends Command
{
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $length = 32;

        $a = '0123456789abcdef';
        $secret = '';
        for ($i = 0; $i < $length; $i++) {
            $secret .= $a[rand(0, 15)];
        }

        $io->success('New JWT passphrase was generated: ' . $secret);

        return 0;
    }
}
