<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Command;

use designerei\ContaoTailwindBridgeBundle\Loader\FieldLoader;
use designerei\ContaoTailwindBridgeBundle\Loader\ThemeLoader;
use designerei\ContaoTailwindBridgeBundle\Loader\UtilityLoader;
use designerei\ContaoTailwindBridgeBundle\Resolver\FieldResolver;
use designerei\ContaoTailwindBridgeBundle\Resolver\UtilityResolver;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'tailwind:debug:fields',
    description: 'Display Tailwind field definitions with resolved options.'
)]
final class DebugFieldsCommand extends Command
{
    public function __construct(
        private readonly ThemeLoader $themeLoader,
        private readonly UtilityLoader $utilityLoader,
        private readonly FieldLoader $fieldLoader,
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
        $fieldsPath = $basePath . '/fields.yaml';

        if (!file_exists($themePath) || !file_exists($utilitiesPath) || !file_exists($fieldsPath)) {
            $output->writeln('<error>Missing YAML configuration files in:</error>');
            $output->writeln('  ' . $basePath);
            return Command::FAILURE;
        }

        $theme = $this->themeLoader->load($themePath);
        $utilities = $this->utilityLoader->load($utilitiesPath, $theme);
        $utilityResolver = new UtilityResolver($theme);
        $fieldResolver = new FieldResolver($utilityResolver, $theme->prefix);
        $fields = $this->fieldLoader->load($fieldsPath);

        foreach ($fields as $field) {
            $result = $fieldResolver->resolve($field, $utilities);

            $output->writeln('');
            $output->writeln('<info>' . $result->key . '</info>');

            if ($result->default) {
                $output->writeln('  default: ' . $result->default);
            }

            $output->writeln('  options:');
            foreach ($result->options as $option) {
                $output->writeln('    ' . $option);
            }

            if ($result->reference) {
                $output->writeln('  references:');
                foreach ($result->reference as $refKey => $label) {
                    $output->writeln('    ' . $refKey . ' => ' . $label);
                }
            }
        }

        return Command::SUCCESS;
    }
}