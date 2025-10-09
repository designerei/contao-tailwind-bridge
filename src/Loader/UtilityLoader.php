<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Loader;

use designerei\ContaoTailwindBridgeBundle\Model\UtilityDefinition;
use designerei\ContaoTailwindBridgeBundle\Model\ThemeDefinition;

final class UtilityLoader extends AbstractLoader
{
    public function load(string $path, ThemeDefinition $theme): array
    {
        $data = $this->loadYaml($path, 'Tailwind utilities file');

        if ($data === null) {
            return [];
        }

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