<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Resolver;

use designerei\ContaoTailwindBridgeBundle\Model\ThemeDefinition;
use designerei\ContaoTailwindBridgeBundle\Model\UtilityDefinition;

final class UtilityResolver
{
    public function __construct(
        private readonly ThemeDefinition $theme,
    ) {
    }

    public function resolve(UtilityDefinition $utility): array
    {
        $values = $this->resolveValues($utility->values);
        $prefix = $this->buildPrefix();
        $classes = $this->buildBaseClasses($utility->names, $values, $prefix);

        if ($utility->responsive) {
            $classes = array_merge($classes, $this->buildResponsiveClasses($utility->names, $values, $prefix));
        }

        return array_values(array_unique($classes));
    }

    private function buildPrefix(): string
    {
        return $this->theme->prefix ? $this->theme->prefix . '-' : '';
    }

    private function buildBaseClasses(array $names, array $values, string $prefix): array
    {
        $classes = [];

        foreach ($names as $name) {
            foreach ($values as $value) {
                $classes[] = $prefix . $name . '-' . $value;
            }
        }

        return $classes;
    }

    private function buildResponsiveClasses(array $names, array $values, string $prefix): array
    {
        $classes = [];

        foreach ($this->theme->breakpoints as $breakpoint) {
            foreach ($names as $name) {
                foreach ($values as $value) {
                    $classes[] = $breakpoint . ':' . $prefix . $name . '-' . $value;
                }
            }
        }

        return $classes;
    }

    private function resolveValues(array|string $values): array
    {
        if (is_string($values) && str_starts_with($values, 'theme.')) {
            $key = substr($values, 6);
            return $this->theme->{$key} ?? [];
        }

        return (array) $values;
    }

    public function getTheme(): ThemeDefinition
    {
        return $this->theme;
    }
}