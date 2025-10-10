<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Generator;


use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use designerei\ContaoTailwindBridgeBundle\Resolver\UtilitiesResolver;
use designerei\ContaoTailwindBridgeBundle\Resolver\ConfigResolver;

final class SafelistGenerator
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        private readonly UtilitiesResolver $utilities,
        private readonly ConfigResolver $config,
    ) {}

    public function generate(): string
    {
        $classes = $this->utilities->resolveUtilitiesClasses();

        if (empty($classes)) {
            throw new \RuntimeException('No classes were generated for the Tailwind safelist.');
        }

        $content = implode(' ', $classes);
        $path = $this->buildFilePath();
        $this->writeFile($path, $content);

        return $path;
    }

    private function buildFilePath(): string
    {
        $relativeDir = $this->config->getSafelist('dir') ?? 'var/tailwind';
        $filename = ($this->config->getSafelist('filename') ?? 'safelist') . '.txt';

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