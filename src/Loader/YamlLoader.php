<?php

declare(strict_types=1);

namespace designerei\ContaoTailwindBridgeBundle\Loader;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;

class YamlLoader
{
    protected string $configDir;
    protected CacheInterface $cache;

    public function __construct(#[Autowire('%kernel.project_dir%')] string $configDir, CacheInterface $cache)
    {
        $this->configDir = rtrim($configDir . '/config/tailwind_bridge', '/');
        $this->cache = $cache;
    }

    public function loadYaml(string $filename): array
    {
        $path = $this->configDir . '/' . $filename;
        $cacheKey = 'config_' . md5($path . '|' . $filename);

        return $this->cache->get($cacheKey, function () use ($path) {
            return Yaml::parseFile($path);
        });
    }
}