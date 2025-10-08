<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use designerei\ContaoTailwindBridgeBundle\Loader\FieldLoader;
use designerei\ContaoTailwindBridgeBundle\Loader\ThemeLoader;
use designerei\ContaoTailwindBridgeBundle\Loader\UtilityLoader;
use designerei\ContaoTailwindBridgeBundle\Resolver\FieldResolver;
use designerei\ContaoTailwindBridgeBundle\Resolver\UtilityResolver;

final class FieldsCallback
{
    public function __construct(
        private readonly ThemeLoader $themeLoader,
        private readonly UtilityLoader $utilityLoader,
        private readonly FieldLoader $fieldLoader,
    ) {
    }

    #[AsCallback(table: 'tl_content', target: 'config.onload')]
    public function onLoadTlContent(): void
    {
        $this->applyFieldConfigToDca('tl_content');
    }

    #[AsCallback(table: 'tl_article', target: 'config.onload')]
    public function onLoadTlArticle(): void
    {
        $this->applyFieldConfigToDca('tl_article');
    }

    private function applyFieldConfigToDca(string $table): void
    {
        $dcaFields = &$GLOBALS['TL_DCA'][$table]['fields'];

        // use relative paths from project dir (Variant A)
        $theme     = $this->themeLoader->load('config/tailwind_bridge/theme.yaml');
        $utilities = $this->utilityLoader->load('config/tailwind_bridge/utilities.yaml', $theme);
        $fields    = $this->fieldLoader->load('config/tailwind_bridge/fields.yaml');

        $utilityResolver = new UtilityResolver($theme);
        $fieldResolver   = new FieldResolver($utilityResolver);

        foreach ($fields as $field) {
            $result = $fieldResolver->resolve($field, $utilities);
            $fieldName = $this->snakeToCamelCase($result->key);

            if (!isset($dcaFields[$fieldName])) {
                continue;
            }

            $this->updateDcaField($dcaFields[$fieldName], $result);
        }
    }

    private function updateDcaField(array &$dcaField, object $result): void
    {
        if (!empty($result->options)) {
            $dcaField['options'] = $this->groupByBreakpoint($result->options);
        }

        if (!empty($result->default)) {
            $dcaField['default'] = $result->default;
        }

        if (!empty($result->reference)) {
            $dcaField['reference'] = $result->reference;
        }
    }

    private function groupByBreakpoint(array $options): array
    {
        $grouped = ['General' => []];

        foreach ($options as $option) {
            if (str_contains($option, ':')) {
                [$breakpoint, $class] = explode(':', $option, 2);
                $grouped[$breakpoint][] = $option;
            } else {
                $grouped[''][] = $option;
            }
        }

        return array_filter($grouped);
    }

    private function snakeToCamelCase(string $input): string
    {
        return lcfirst(str_replace('_', '', ucwords($input, '_')));
    }
}