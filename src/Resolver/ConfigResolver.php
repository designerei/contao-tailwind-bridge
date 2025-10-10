<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Resolver;

use designerei\ContaoTailwindBridgeBundle\Loader\YamlLoader;

class ConfigResolver
{
    protected string $filename = 'config.yaml';

    public function __construct(
        protected YamlLoader $yamlLoader,
    ) {}

    public function loadConfig(): array
    {
        return $this->yamlLoader->loadYaml($this->filename);
    }

    public function getPrefix(): ?string
    {
        $config = $this->loadConfig();
        return $config['config']['prefix'] ?? null;
    }

    public function hasPrefix(): bool
    {
        return !empty($this->getPrefix());
    }

    public function getTheme(?string $key = null): mixed
    {
        $config = $this->loadConfig();
        $theme = $config['config']['theme'] ?? [];

        if ($key === null) {
            return $theme;
        }

        return $theme[$key] ?? null;
    }

    public function hasTheme(): bool
    {
        return !empty($this->getTheme());
    }

    public function getSafelist(?string $key = null): mixed
    {
        $config = $this->loadConfig();
        $safelist = $config['config']['safelist'] ?? [];

        if ($key === null) {
            return $safelist;
        }

        return $safelist[$key] ?? null;
    }

    public function hasSafelist(): bool
    {
        return !empty($this->getSafelist());
    }
}