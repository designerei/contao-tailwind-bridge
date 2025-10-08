<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Command;

use designerei\ContaoTailwindBridgeBundle\Loader\ThemeLoader;
use designerei\ContaoTailwindBridgeBundle\Loader\UtilityLoader;
use designerei\ContaoTailwindBridgeBundle\Resolver\UtilityResolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'tailwind:debug:utilities',
    description: 'Display all Tailwind utilities with resolved CSS classes.'
)]
final class DebugUtilitiesCommand extends Command
{
    public function __construct(
        private readonly ThemeLoader $themeLoader,
        private readonly UtilityLoader $utilityLoader,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $kernel = $this->getApplication()?->getKernel();
        $projectDir = $kernel?->getProjectDir();

        if (!$projectDir) {
            $output->writeln('<error>Could not resolve the project directory.</error>');
            return Command::FAILURE;
        }

        $basePath = $projectDir . '/config/tailwind_bridge';
        $themePath = $basePath . '/theme.yaml';
        $utilitiesPath = $basePath . '/utilities.yaml';

        if (!file_exists($themePath) || !file_exists($utilitiesPath)) {
            $output->writeln('<error>Missing YAML configuration files in:</error>');
            $output->writeln('  ' . $basePath);
            return Command::FAILURE;
        }

        $theme = $this->themeLoader->load($themePath);
        $utilities = $this->utilityLoader->load($utilitiesPath, $theme);
        $resolver = new UtilityResolver($theme);

        foreach ($utilities as $key => $utility) {
            $output->writeln('');
            $output->writeln('<info>' . $key . '</info>');

            $classes = $resolver->resolve($utility);

            if (empty($classes)) {
                $output->writeln('  (no classes generated)');
                continue;
            }

            foreach ($classes as $class) {
                $output->writeln('  ' . $class);
            }
        }

        return Command::SUCCESS;
    }
}