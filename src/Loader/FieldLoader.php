<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Loader;

use designerei\ContaoTailwindBridgeBundle\Model\FieldDefinition;

final class FieldLoader extends AbstractLoader
{
    public function load(string $path): array
    {
        $data = $this->loadYaml($path, 'Tailwind fields file');

        if ($data === null) {
            return [];
        }

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