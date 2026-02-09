<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\EventListener\DataContainer;

use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use designerei\ContaoTailwindBridgeBundle\Resolver\UtilitiesResolver;
use designerei\ContaoTailwindBridgeBundle\Resolver\FieldsResolver;
use designerei\ContaoTailwindBridgeBundle\Resolver\ConfigResolver;

class FieldsCallback
{
    public function __construct(
        private readonly UtilitiesResolver $utilities,
        private readonly FieldsResolver $fields,
        private readonly ConfigResolver $config,
    ) {
    }

    #[AsCallback(table: 'tl_article', target: 'config.onload')]
    public function onLoadTlArticle(): void
    {
        $this->applyFieldConfigToDca('tl_article');
    }

    #[AsCallback(table: 'tl_content', target: 'config.onload')]
    public function onLoadTlContent(): void
    {
        $this->applyFieldConfigToDca('tl_content');
    }

    private function applyFieldConfigToDca(string $table): void
    {
        $dcaFields = &$GLOBALS['TL_DCA'][$table]['fields'];
        $fields = $this->fields->resolveFields();

        foreach ($fields as $key => $values) {
            $key = $this->snakeToCamelCase($key);

            if (!isset($dcaFields[$key])) {
                continue;
            }

            $this->updateDcaField($dcaFields[$key], $values);
        }
    }

    private function updateDcaField(array &$dcaField, array $values): void
    {
        if (!empty($values['options'])) {
            $dcaField['options'] = $this->groupByBreakpoint($values['options']);
        }

        if (!empty($values['default'])) {
            $dcaField['default'] = $values['default'];
        }

        if (!empty($values['reference'])) {
            $dcaField['reference'] = $values['reference'];
        }
    }

    private function snakeToCamelCase(string $input): string
    {
        return lcfirst(str_replace('_', '', ucwords($input, '_')));
    }

    private function groupByBreakpoint(array $options): array
    {
        $hasBreakpoints = false;
        $grouped = ['General' => []];

        foreach ($options as $option) {
            if (str_contains($option, ':')) {
                $hasBreakpoints = true;
                [$breakpoint] = explode(':', $option, 2);
                $grouped[$breakpoint][] = $option;
            } else {
                $grouped['—'][] = $option;
            }
        }

        if (!$hasBreakpoints) {
            return $options;
        }

        return array_filter($grouped);
    }
}