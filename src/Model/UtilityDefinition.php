<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Model;

final class UtilityDefinition
{
    public function __construct(
        public readonly string $key,
        public readonly array $names,
        public readonly array|string $values,
        public readonly bool $responsive = false,
    ) {
    }
}