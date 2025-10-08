<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Command;

use designerei\ContaoTailwindBridgeBundle\Loader\ThemeLoader;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'tailwind:debug:theme',
    description: 'Display Tailwind theme configuration (prefix, breakpoints, spacing, etc.).'
)]
final class DebugThemeCommand extends Command
{
    public function __construct(
        private readonly ThemeLoader $themeLoader,
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

        $themePath = $projectDir . '/config/tailwind_bridge/theme.yaml';

        if (!file_exists($themePath)) {
            $output->writeln('<error>Theme configuration file not found:</error>');
            $output->writeln('  ' . $themePath);
            return Command::FAILURE;
        }

        $theme = $this->themeLoader->load($themePath);

        $output->writeln('<info>Tailwind Theme Configuration</info>');
        $output->writeln('');

        if ($theme->prefix) {
            $output->writeln('Prefix:       ' . $theme->prefix);
        }

        if (!empty($theme->breakpoints)) {
            $output->writeln('Breakpoints:  ' . implode(', ', $theme->breakpoints));
        }

        if (!empty($theme->spacing)) {
            $output->writeln('Spacing:      ' . implode(', ', $theme->spacing));
        }

        return Command::SUCCESS;
    }
}