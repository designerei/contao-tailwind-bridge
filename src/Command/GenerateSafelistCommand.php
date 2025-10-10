<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Command;

use designerei\ContaoTailwindBridgeBundle\Generator\SafelistGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'tailwind:generate:safelist',
    description: 'Generate a safelist.txt file from all configured Tailwind utilities.'
)]
final class GenerateSafelistCommand extends Command
{
    public function __construct(
        private readonly SafelistGenerator $generator,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $path = $this->generator->generate([]);
            $output->writeln(sprintf('<info>Safelist generated successfully at:</info> %s', $path));
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln(sprintf('<error>Failed to generate safelist:</error> %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}