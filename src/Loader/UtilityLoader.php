<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Loader;

use designerei\ContaoTailwindBridgeBundle\Model\UtilityDefinition;
use designerei\ContaoTailwindBridgeBundle\Model\ThemeDefinition;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

final class UtilityLoader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {}

    public function load(string $path, ThemeDefinition $theme): array
    {
        // Wenn Pfad relativ ist → prepend project_dir
        if (!str_starts_with($path, '/')) {
            $path = $this->projectDir . '/' . ltrim($path, '/');
        }

        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf('Tailwind utilities file not found: %s', $path));
        }

        $data = Yaml::parseFile($path);
        $utilities = $data['utilities'] ?? [];

        $definitions = [];

        foreach ($utilities as $key => $config) {
            $names = $config['names'] ?? [];
            $values = $config['values'] ?? [];
            $responsive = (bool) ($config['responsive'] ?? false);

            if (is_string($values) && str_starts_with($values, 'theme.')) {
                $property = substr($values, 6);
                $values = $theme->{$property} ?? [];
            }

            if (is_string($names)) {
                $names = [$names];
            }

            if (!is_array($values)) {
                $values = [$values];
            }

            $definitions[$key] = new UtilityDefinition(
                key: $key,
                names: $names,
                values: $values,
                responsive: $responsive,
            );
        }

        return $definitions;
    }
}