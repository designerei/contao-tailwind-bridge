<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Twig\Extension;

use designerei\ContaoTailwindBridgeBundle\Resolver\ConfigResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

final class ContaoTailwindBridgeExtension extends AbstractExtension
{
    public function __construct(
        private readonly ConfigResolver $config,
    ) {}

    public function getFilters(): array
    {
        return [
            new TwigFilter('tailwind_prefix', [$this, 'addTailwindPrefix']),
        ];
    }

    public function addTailwindPrefix(string|array $value): string|array
    {
        $prefix = $this->config->hasPrefix() ? $this->config->getPrefix() . '-' : '';

        if ($prefix === '') {
            return $value;
        }

        if (is_string($value)) {
            return $this->prefixStringIfNeeded($value, $prefix);
        }

        $result = [];
        foreach ($value as $key => $val) {
            if (is_string($val)) {
                $result[$key] = $this->prefixStringIfNeeded($val, $prefix);
            } elseif (is_array($val)) {
                $result[$key] = $this->addTailwindPrefix($val);
            } else {
                $result[$key] = $val;
            }
        }

        return $result;
    }

    private function prefixStringIfNeeded(string $value, string $prefix): string
    {
        $lastSep = strrpos($value, ':');
        $segment = $lastSep === false ? $value : substr($value, $lastSep + 1);

        if (str_starts_with($segment, $prefix)) {
            return $value;
        }

        return ($lastSep === false)
            ? $prefix . $value
            : substr($value, 0, $lastSep + 1) . $prefix . $segment;
    }
}