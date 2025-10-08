<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Model;

final class FieldDefinition
{
    public function __construct(
        public readonly string $key,
        public readonly array $options = [],
        public readonly ?string $default = null,
        public readonly array $reference = [],
    ) {
    }
}