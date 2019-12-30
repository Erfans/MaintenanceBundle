<?php

namespace Erfans\MaintenanceBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class ErfansMaintenanceExtension extends Extension
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

        $definition = $container->getDefinition("Erfans\MaintenanceBundle\EventListener\MaintenanceListener");
        $definition->addArgument($config);

        $container->setParameter("erfans.maintenance.parameters.view.title", $config["view"]["title"]);
        $container->setParameter("erfans.maintenance.parameters.view.description", $config["view"]["description"]);
    }
}
