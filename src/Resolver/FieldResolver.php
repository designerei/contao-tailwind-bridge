<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Resolver;

use designerei\ContaoTailwindBridgeBundle\Model\FieldDefinition;
use designerei\ContaoTailwindBridgeBundle\Model\FieldResult;

final class FieldResolver
{
    public function __construct(
        private readonly UtilityResolver $utilityResolver,
    ) {
    }

    public function resolve(FieldDefinition $field, array $utilities): FieldResult
    {
        $options = $this->buildOptions($field->options, $utilities);

        $reference = !empty($field->reference)
            ? $this->buildReferences($field->reference)
            : [];

        $default = $field->default
            ? $this->buildDefault($field->default, $options)
            : null;

        return new FieldResult(
            key: $field->key,
            options: $options,
            default: $default,
            reference: $reference
        );
    }

    private function buildOptions(array $optionsConfig, array $utilities): array
    {
        $options = [];

        foreach ($optionsConfig as $option) {
            if (is_string($option) && str_starts_with($option, 'utilities.')) {
                $key = substr($option, 10);

                if (!isset($utilities[$key])) {
                    continue;
                }

                $classes = $this->utilityResolver->resolve($utilities[$key], false);
                $options = array_merge($options, $classes);
            } else {
                $options[] = $option;
            }
        }

        return array_values(array_unique($options));
    }

    private function buildReferences(array $references): array
    {
        return $references;
    }

    private function buildDefault(string $default, array $options = []): ?string
    {
        return in_array($default, $options, true) ? $default : null;
    }
}