<?php

namespace designerei\ContaoTailwindBridgeBundle\ContaoManager;

use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\CoreBundle\ContaoCoreBundle;
use designerei\ContaoTailwindBridgeBundle\ContaoTailwindBridgeBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ContaoTailwindBridgeBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}