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
        $prefix = $this->buildPrefix();
        $options = $this->buildOptions($field->options, $utilities, $prefix);

        $reference = !empty($field->reference)
            ? $this->buildReferences($field->reference, $options, $prefix)
            : [];

        $default = $field->default
            ? $this->buildDefault($field->default, $prefix, $options)
            : null;

        return new FieldResult(
            key: $field->key,
            options: $options,
            default: $default,
            reference: $reference
        );
    }

    private function buildPrefix(): string
    {
        $prefix = $this->utilityResolver->getTheme()->prefix;
        return $prefix ? $prefix . '-' : '';
    }

    private function buildOptions(array $optionsConfig, array $utilities, string $prefix): array
    {
        $options = [];

        foreach ($optionsConfig as $option) {
            if (is_string($option) && str_starts_with($option, 'utilities.')) {
                $key = substr($option, 10);

                if (!isset($utilities[$key])) {
                    continue;
                }

                $classes = $this->utilityResolver->resolve($utilities[$key]);
                $options = array_merge($options, $classes);
            } else {
                $options[] = $prefix . $option;
            }
        }

        return array_values(array_unique($options));
    }

    private function buildReferences(array $references, array $options, string $prefix): array
    {
        $result = [];

        foreach ($references as $key => $label) {
            $result[$prefix . $key] = $label;
        }

        return $result;
    }

    private function buildDefault(string $default, string $prefix, array $options = []): ?string
    {
        $prefixedDefault = $prefix . $default;

        if (in_array($prefixedDefault, $options, true)) {
            return $prefixedDefault;
        }

        return null;
    }
}