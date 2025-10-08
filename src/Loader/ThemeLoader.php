<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Loader;

use designerei\ContaoTailwindBridgeBundle\Model\ThemeDefinition;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

final class ThemeLoader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {}

    public function load(?string $path = null): ThemeDefinition
    {
        if ($path === null) {
            $path = $this->projectDir . '/config/tailwind_bridge/theme.yaml';
        }

        if (!str_starts_with($path, '/')) {
            $path = $this->projectDir . '/' . ltrim($path, '/');
        }

        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf(
                'Tailwind theme configuration not found at path: %s',
                $path
            ));
        }

        $data = Yaml::parseFile($path);
        $themeData = $data['theme'] ?? [];

        return new ThemeDefinition(
            prefix: $themeData['prefix'] ?? null,
            breakpoints: $themeData['breakpoints'] ?? [],
            spacing: $themeData['spacing'] ?? [],
            safelist: $themeData['safelist'] ?? [],
        );
    }
}