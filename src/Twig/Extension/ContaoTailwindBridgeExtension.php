<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Twig\Extension;

use designerei\ContaoTailwindBridgeBundle\Loader\ThemeLoader;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Contao\StringUtil;

final class ContaoTailwindBridgeExtension extends AbstractExtension
{
    public function __construct(
        private readonly ThemeLoader $themeLoader
    ) {}

    public function getFilters(): array
    {
        return [
            new TwigFilter('prefix', [$this, 'addPrefix']),
            new TwigFilter('join_classes', [$this, 'joinClasses']),
            new TwigFilter('unique_classes', [$this, 'uniqueClasses']),
            new TwigFilter('format_tailwind_classes', [$this, 'formatTailwindClasses']),
        ];
    }

    public function formatTailwindClasses(
        mixed $value,
        bool $prefix = true,
        bool $join = true,
        bool $deserialize = true,
        bool $unique = true,
    ): mixed {
        if ($deserialize && is_string($value)) {
            try {
                $value = StringUtil::deserialize($value);
            } catch (\Throwable) {}
        }

        if ($unique) {
            $value = $this->uniqueClasses($value);
        }

        if ($prefix) {
            $value = $this->addPrefix($value);
        }

        if ($join) {
            $value = $this->joinClasses($value);
        }

        return $value;
    }

    public function addPrefix(mixed $value): mixed
    {
        $theme = $this->themeLoader->load();
        $prefix = $theme->prefix ?? '';

        if (empty($prefix) || empty($value)) {
            return $value;
        }

        if (is_array($value)) {
            return array_map(fn ($v) => $this->applyPrefix($v, $prefix), $value);
        }

        return $this->applyPrefix((string) $value, $prefix);
    }

    public function joinClasses(mixed $value): string
    {
        if (is_array($value)) {
            return trim(implode(' ', array_filter($value)));
        }

        return trim((string) $value);
    }

    public function uniqueClasses(mixed $value): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        return array_values(array_unique(array_filter($value)));
    }

    private function applyPrefix(string $class, string $prefix): string
    {
        $prefix = rtrim($prefix, '-') . '-';

        if (preg_match('/^([a-z0-9_-]+:)(.+)$/', $class, $matches)) {
            $breakpoint = $matches[1];
            $rest = $matches[2];

            return str_starts_with($rest, $prefix)
                ? $class
                : $breakpoint . $prefix . $rest;
        }

        return str_starts_with($class, $prefix) ? $class : $prefix . $class;
    }
}
