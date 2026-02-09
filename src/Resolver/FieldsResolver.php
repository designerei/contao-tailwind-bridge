<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Resolver;

use designerei\ContaoTailwindBridgeBundle\Loader\YamlLoader;
use designerei\ContaoTailwindBridgeBundle\Resolver\UtilitiesResolver;

class FieldsResolver
{
    protected string $filename = 'fields.yaml';

    public function __construct(
        protected YamlLoader $yamlLoader,
        protected UtilitiesResolver $utilities,
    ) {}

    public function loadFields(): array
    {
        return $this->yamlLoader->loadYaml($this->filename);
    }

    public function loadField(string $field): array
    {
        $utilities = $this->loadFields();

        return $utilities['fields'][$field] ?? [];
    }

    public function getFieldDefault(string $field): string
    {
        return $this->loadField($field)['default'] ?? '';
    }

    public function getFieldOptions(string $field): array|string
    {
        $options = (array)$this->loadField($field)['options'] ?? [];
        $resolvedOptions = [];

        foreach ($options as $option) {
            if (str_starts_with($option, 'utilities.')) {
                $key = substr($option, 10);
                $resolvedOptions = array_merge($resolvedOptions, $this->utilities->resolveUtilityClasses($key, false));
            } else {
                $resolvedOptions[] = $option;
            }
        }

        return $resolvedOptions;
    }

    public function getFieldReference(string $field): array
    {
        $references = $this->loadField($field)['reference'] ?? [];

        if (!is_array($references) || $references === []) {
            return [];
        }

        return array_merge(...array_filter($references, 'is_array'));
    }

    public function resolveField(string $field): array
    {
        $field = [
            'default' => $this->getFieldDefault($field),
            'options' => $this->getFieldOptions($field),
            'reference' => $this->getFieldReference($field),
        ];

        return array_filter($field);
    }

    public function resolveFields(): array
    {
        $fields = array_keys($this->loadFields()['fields']);

        $resolvedFields = [];

        foreach ($fields as $field) {
            $resolvedFields[$field] = $this->resolveField($field);
        }

        return $resolvedFields;
    }
}