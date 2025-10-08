<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Model;

final class ThemeDefinition
{
    public function __construct(
        public readonly ?string $prefix = null,
        public readonly array $breakpoints = [],
        public readonly array $spacing = [],
        public readonly array $safelist = [],
    ) {
    }
}