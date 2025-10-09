<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Loader;

use designerei\ContaoTailwindBridgeBundle\Model\ThemeDefinition;

final class ThemeLoader extends AbstractLoader
{
    public function load(?string $path = null): ThemeDefinition
    {
        if ($path === null) {
            $path = $this->projectDir . '/config/tailwind_bridge/theme.yaml';
        }

        $data = $this->loadYaml($path, 'Tailwind theme configuration');

        if ($data === null) {
            return new ThemeDefinition(
                prefix: null,
                breakpoints: [],
                spacing: [],
                safelist: [],
            );
        }

        $themeData = $data['theme'] ?? [];

        return new ThemeDefinition(
            prefix: $themeData['prefix'] ?? null,
            breakpoints: $themeData['breakpoints'] ?? [],
            spacing: $themeData['spacing'] ?? [],
            safelist: $themeData['safelist'] ?? [],
        );
    }
}