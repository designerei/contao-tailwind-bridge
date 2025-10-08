<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Generator;

use designerei\ContaoTailwindBridgeBundle\Model\ThemeDefinition;
use designerei\ContaoTailwindBridgeBundle\Model\UtilityDefinition;
use designerei\ContaoTailwindBridgeBundle\Resolver\UtilityResolver;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

final class SafelistGenerator
{
    public function __construct(
        private readonly UtilityResolver $utilityResolver,
        private readonly ThemeDefinition $theme,

        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {
    }

    public function generate(array $utilities): string
    {
        $classes = $this->collectClasses($utilities);

        if (empty($classes)) {
            throw new \RuntimeException('No classes were generated for the Tailwind safelist.');
        }

        $content = $this->buildContent($classes);
        $path = $this->buildFilePath();

        $this->writeFile($path, $content);

        return $path;
    }

    private function collectClasses(array $utilities): array
    {
        $classes = [];

        foreach ($utilities as $utility) {
            if (!$utility instanceof UtilityDefinition) {
                continue;
            }

            $resolved = $this->utilityResolver->resolve($utility);
            if (!empty($resolved)) {
                $classes[] = $resolved;
            }
        }

        // Flatten nested arrays and remove duplicates
        return array_values(array_unique(array_merge(...$classes)));
    }

    private function buildContent(array $classes): string
    {
        $isMinified = (bool) ($this->theme->safelist['minified'] ?? true);

        return $isMinified
            ? implode(' ', $classes)
            : implode(PHP_EOL, $classes);
    }

    private function buildFilePath(): string
    {
        $relativeDir = $this->theme->safelist['dir'] ?? 'var/tailwind';
        $filename = ($this->theme->safelist['filename'] ?? 'safelist') . '.txt';

        $dir = rtrim($this->projectDir . '/' . ltrim($relativeDir, '/'), '/');
        $path = $dir . '/' . $filename;

        $filesystem = new Filesystem();

        if (!$filesystem->exists($dir)) {
            try {
                $filesystem->mkdir($dir, 0777);
            } catch (\Throwable $e) {
                throw new \RuntimeException(sprintf(
                    'Failed to create safelist directory: %s (%s)',
                    $dir,
                    $e->getMessage()
                ));
            }
        }

        return $path;
    }

    private function writeFile(string $path, string $content): void
    {
        $result = @file_put_contents($path, $content);

        if ($result === false) {
            throw new \RuntimeException(sprintf('Unable to write safelist file at path: %s', $path));
        }
    }
}