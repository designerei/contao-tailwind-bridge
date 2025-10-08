<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Command;

use designerei\ContaoTailwindBridgeBundle\Generator\SafelistGenerator;
use designerei\ContaoTailwindBridgeBundle\Loader\ThemeLoader;
use designerei\ContaoTailwindBridgeBundle\Loader\UtilityLoader;
use designerei\ContaoTailwindBridgeBundle\Resolver\UtilityResolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'tailwind:generate:safelist',
    description: 'Generate a safelist.txt file from all configured Tailwind utilities.'
)]
final class GenerateSafelistCommand extends Command
{
    public function __construct(
        private readonly ThemeLoader $themeLoader,
        private readonly UtilityLoader $utilityLoader,
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configPath = $this->projectDir . '/config/tailwind_bridge';
        $themePath = $configPath . '/theme.yaml';
        $utilitiesPath = $configPath . '/utilities.yaml';

        if (!file_exists($themePath) || !file_exists($utilitiesPath)) {
            $output->writeln('<error>❌ Missing Tailwind configuration files.</error>');
            $output->writeln('Expected paths:');
            $output->writeln('  ' . $themePath);
            $output->writeln('  ' . $utilitiesPath);
            return Command::FAILURE;
        }

        try {
            $theme = $this->themeLoader->load($themePath);
            $utilities = $this->utilityLoader->load($utilitiesPath, $theme);
            $utilityResolver = new UtilityResolver($theme);
            $generator = new SafelistGenerator($utilityResolver, $theme, $this->projectDir);

            $path = $generator->generate($utilities);
        } catch (\Throwable $e) {
            $output->writeln('<error>⚠️ Failed to generate safelist:</error>');
            $output->writeln('  ' . $e->getMessage());
            return Command::FAILURE;
        }

        $fileSize = $this->getReadableFileSize($path);

        $output->writeln('');
        $output->writeln('<info>✅ Safelist generated successfully:</info>');
        $output->writeln('  Path: ' . $path);
        $output->writeln('  Size: ' . $fileSize);
        $output->writeln('');

        return Command::SUCCESS;
    }

    private function getReadableFileSize(string $path): string
    {
        if (!file_exists($path)) {
            return 'unknown';
        }

        $bytes = filesize($path);
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $factor = (int) floor((strlen((string) $bytes) - 1) / 3);

        return sprintf('%.2f %s', $bytes / pow(1024, $factor), $units[$factor]);
    }
}