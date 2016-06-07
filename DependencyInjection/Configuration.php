<?php

namespace Efi\MaintenanceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('efi_maintenance');

        $rootNode
            ->children()
                ->booleanNode("maintenance_mode")
                    ->defaultFalse()
                ->end()
                ->integerNode("due_date")
                    ->defaultNull()
                    ->info("After due-date maintenance mode will not invoke anymore. ".
                        "Date format should be 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DD HH:MM:SS +/-TT:TT' or timestamp")
                    ->example("2016-7-6 or 2016-7-6 10:10 or 2016-7-6 10:10:10 +02:00 or 1467763200")
                    ->validate()
                    ->ifString()
                        ->thenInvalid('"%s" is in incorrect format for due_date')
                    ->end()
                ->end()
                ->arrayNode("view")
                    ->info("View parameters will set on default twig template of maintenance bundle. These values translate before rendering")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode("title")
                            ->defaultValue("efi.maintenance.messages.under_construction.title")
                        ->end()
                        ->scalarNode("description")
                            ->defaultValue("efi.maintenance.messages.under_construction.description")
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("redirect_on_normal")
                    ->info("If maintenance mode is false or it is after due date then it will redirect to below path or url by requesting maintenance page.")
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) { return array('available' => $v); })
                    ->end()
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode("available")
                            ->defaultTrue()
                        ->end()
                        ->scalarNode("redirect_url")
                            ->defaultValue("/")
                        ->end()
                        ->scalarNode("redirect_route")
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("authorized_users")
                    ->info("While the website is in maintenance mode it is possible to allow some users to visit the website based on users' roles or usernames.")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode("roles")
                            ->prototype('scalar')->end()
                            ->defaultValue(["ROLE_ADMIN","ROLE_SUPER_ADMIN"])
                        ->end()
                        ->arrayNode("usernames")
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode("authorized_areas")
                    ->info("You may like exclude some pages from maintenance mode. Here you can define their paths or routes.")
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode("paths")
                            ->prototype('scalar')->end()
                            ->defaultValue(["/login"])
                        ->end()
                        ->arrayNode("routes")
                            ->prototype('scalar')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
