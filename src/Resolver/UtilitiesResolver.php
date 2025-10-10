<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Resolver;

use designerei\ContaoTailwindBridgeBundle\Loader\YamlLoader;
use designerei\ContaoTailwindBridgeBundle\Resolver\ConfigResolver;

class UtilitiesResolver
{
    protected string $filename = 'utilities.yaml';

    public function __construct(
        protected YamlLoader     $yamlLoader,
        protected ConfigResolver $config,
    ) {}

    public function loadUtilities(): array
    {
        return $this->yamlLoader->loadYaml($this->filename);
    }

    public function loadUtility(string $utility): array
    {
        $utilities = $this->loadUtilities();

        return $utilities['utilities'][$utility] ?? [];
    }

    public function getUtilityNames(string $utility): array|string
    {
        return (array)$this->loadUtility($utility)['names'] ?? [];
    }

    public function getUtilityValues(string $utility): array|string
    {
        $values = (array) $this->loadUtility($utility)['values'] ?? [];
        $resolvedValues = [];

        foreach ($values as $value) {
            if (str_starts_with($value, 'theme.')) {
                $key = substr($value, 6);
                $resolvedValues = array_merge($resolvedValues, $this->config->getTheme($key));
            } else {
                $resolvedValues[] = $value;
            }
        }

        return $resolvedValues;
    }

    public function isUtilityResponsive(string $utility): bool
    {
        return $this->loadUtility($utility)['responsive'] ?? false;
    }

    public function resolveUtilityClasses(string $utility): array
    {
        $names = $this->getUtilityNames($utility);
        $values = $this->getUtilityValues($utility);
        $isResponsive = $this->isUtilityResponsive($utility) ?? false;
        $prefix = $this->config->hasPrefix() ? $this->config->getPrefix() . '-' : '';
        $classes = [];

        foreach ($names as $name) {

            foreach ($values as $value) {
                $classes[] = $prefix . $name . '-' . $value;
            }
        }

        if ($isResponsive) {
            $breakpoints = $this->config->getTheme('breakpoints');
            $responsiveClasses = [];

            foreach ($breakpoints as $breakpoint) {
                foreach ($names as $name) {
                    foreach ($values as $value) {
                        $classes[] = $breakpoint . ':' . $prefix . $name . '-' . $value;
                    }
                }
            }

            array_merge($classes, $responsiveClasses);
        }

        return $classes;
    }

    public function resolveUtilitiesClasses(): array
    {
        $utilities = array_keys($this->loadUtilities()['utilities']);
        $classes = [];

        foreach ($utilities as $utility) {
            $classes = array_merge($classes, $this->resolveUtilityClasses($utility));
        }

        return $classes;
    }
}