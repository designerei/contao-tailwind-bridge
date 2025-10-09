<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Loader;

use Psr\Log\LoggerInterface;
use Contao\CoreBundle\Monolog\ContaoContext;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

abstract class AbstractLoader
{
    private static array $missingFilesLogged = [];

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        protected readonly string $projectDir,
        #[Autowire(service: 'monolog.logger.contao.configuration')]
        protected readonly LoggerInterface $logger,
    ) {}

    protected function resolvePath(string $path): string
    {
        return str_starts_with($path, '/')
            ? $path
            : $this->projectDir . '/' . ltrim($path, '/');
    }

    protected function loadYaml(string $path, string $context): ?array
    {
        $path = $this->resolvePath($path);

        if (!file_exists($path)) {
            if (!isset(self::$missingFilesLogged[$path])) {
                self::$missingFilesLogged[$path] = true;

                $this->logger->warning(
                    sprintf('%s not found at path: %s — using empty defaults.', $context, $path),
                    ['contao' => new ContaoContext(__METHOD__, ContaoContext::CONFIGURATION)]
                );
            }

            return null;
        }

        return Yaml::parseFile($path);
    }
}