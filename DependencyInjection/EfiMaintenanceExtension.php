<?php

namespace Efi\MaintenanceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class EfiMaintenanceExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $definition = $container->getDefinition("efi.maintenance.maintenance_listener");
        $definition->addArgument($config["maintenance_mode"]);

        $definition->addArgument($config["due_date"]);
        $definition->addArgument($config["authorized_users"]);
        $definition->addArgument($config["authorized_areas"]);

        $container->setParameter("efi.maintenance.parameters.maintenance_mode", $config["maintenance_mode"]);
        $container->setParameter("efi.maintenance.parameters.due_date", $config["due_date"]);
        $container->setParameter("efi.maintenance.parameters.redirect_on_normal", $config["redirect_on_normal"]);
        $container->setParameter("efi.maintenance.parameters.view", $config["view"]);
    }
}
