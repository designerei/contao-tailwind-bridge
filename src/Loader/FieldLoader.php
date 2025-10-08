<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Loader;

use designerei\ContaoTailwindBridgeBundle\Model\FieldDefinition;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Yaml\Yaml;

final class FieldLoader
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
    ) {}

    public function load(string $path): array
    {
        // Wenn Pfad relativ ist → prepend project_dir
        if (!str_starts_with($path, '/')) {
            $path = $this->projectDir . '/' . ltrim($path, '/');
        }

        if (!file_exists($path)) {
            throw new \RuntimeException(sprintf('Tailwind fields file not found: %s', $path));
        }

        $data = Yaml::parseFile($path);
        $fields = $data['fields'] ?? [];

        $definitions = [];

        foreach ($fields as $key => $config) {
            $definitions[$key] = new FieldDefinition(
                key: $key,
                options: $config['options'] ?? [],
                default: $config['default'] ?? null,
                reference: $config['reference'] ?? [],
            );
        }

        return $definitions;
    }
}