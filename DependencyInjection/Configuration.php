<?php

namespace Erfans\MaintenanceBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
        $treeBuilder = new TreeBuilder('erfans_maintenance');
        $rootNode = $treeBuilder->getRootNode();

        //{env: env, route: route_name, path: path, url: url, role: user_role, username: user_username, ip: ip}

        /** ArrayNodeDefinition $rootNode */
        $rootNode
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return isset($v['maintenance_route']) && isset($v['maintenance_url']);
                })
                ->thenInvalid('You cannot use both "maintenance_route" and "maintenance_url" at same time.')
            ->end()
            ->canBeEnabled()
            ->children()
                ->scalarNode("due_date")->defaultNull()
                    ->info(
                        "After due-date maintenance mode will not invoke anymore. ".
                        "Date format should be 'YYYY-MM-DD' or 'YYYY-MM-DD HH:MM:SS' or 'YYYY-MM-DD HH:MM:SS +/-TT:TT' or timestamp"
                    )
                    ->example("2016-7-6 or 2016-7-6 10:10 or 2016-7-6 10:10:10 +02:00 or 1467763200")
                    ->validate()
                        ->ifString()
                            ->thenInvalid('"%s" is in incorrect format for due_date')
                    ->end()
                ->end()
                ->scalarNode("maintenance_route")->defaultValue("erfans_maintenance_maintenance")
                    ->info("It is possible to change corresponding controller by changing the route name.")
                ->end()
                ->scalarNode("maintenance_url")
                    ->info("Maintenance page can be an external link or only an html page.")
                ->end()
            ->end();


        $this->addViewSection($rootNode);
        $this->addRules($rootNode);
        $this->addRedirectSection($rootNode);

        return $treeBuilder;
    }

    private function addViewSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode("view")
                ->info(
                    "View parameters will set on default twig template of maintenance bundle.".
                    " These values will translate before rendering")
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode("title")
                        ->defaultValue("erfans.maintenance.messages.under_construction.title")
                    ->end()
                    ->scalarNode("description")
                        ->defaultValue("erfans.maintenance.messages.under_construction.description")
                    ->end()
                ->end()
            ->end();
    }

    private function addRules(ArrayNodeDefinition $rootNode){

        $rootNode
            ->children()
                ->arrayNode("rules")
                    ->info("To provide maximum flexibility to put part of website on maintenance mode by defining 'include' or 'exclude' rules.")
                    ->example("- {rule: '+', path: '^/*'} # to set maintenance mode for whole website")
                    ->prototype('array')
                        ->children()
                            ->enumNode('rule')->isRequired()->cannotBeEmpty()->values(['+', '-'])->end()
                            ->arrayNode('env')
                                ->beforeNormalization()
                                    ->ifString()->then(function ($v) { return [$v]; })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('path')->end()
                            ->arrayNode('routes')
                                ->beforeNormalization()
                                    ->ifString()->then(function ($v) { return [$v]; })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('host')->end()
                            ->arrayNode('schemes')
                                ->beforeNormalization()
                                    ->ifString()->then(function ($v) { return [$v]; })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('methods')
                                ->beforeNormalization()
                                    ->ifString()->then(function ($v) { return [$v]; })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('usernames')
                                ->beforeNormalization()
                                    ->ifString()->then(function ($v) { return [$v]; })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('roles')
                                ->beforeNormalization()
                                    ->ifString()->then(function ($v) { return [$v]; })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('ips')
                                ->beforeNormalization()
                                    ->ifString()->then(function ($v) { return [$v]; })
                                ->end()
                                ->prototype('scalar')->end()
                            ->end()
                        ->end()
                    ->end()
                    ->defaultValue([
                        ['rule'=>'+', 'path'=>'^/*'],
                        ['rule'=>'-', 'path'=>'^/login$'],
                        ['rule'=>'-', 'roles'=>['ROLE_ADMIN']],
                        ['rule'=>'-', 'env'=>['test','dev']],
                    ])
                ->end();

    }

    private function addRedirectSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->beforeNormalization()
                ->ifTrue(function($v){
                    return isset($v["maintenance_url"]) && isset($v["redirect_on_normal"]["redirect_url"]) &&
                    $v["maintenance_url"] == $v["redirect_on_normal"]["redirect_url"];
                })
                ->thenInvalid('"maintenance_url" and "redirect_url" under "redirect_on_normal" could not be same.'.
                    'it cause a redirect loop.')
                ->end()
            ->beforeNormalization()
                ->ifTrue(function($v){
                    return isset($v["maintenance_url"]) && isset($v["redirect_on_normal"]["redirect_route"]) &&
                    $v["maintenance_route"] == $v["redirect_on_normal"]["redirect_route"];
                })
                ->thenInvalid('"maintenance_route" and "redirect_route" under "redirect_on_normal" could not be same.'.
                    'it cause a redirect loop.')
            ->end()
            ->children()
                ->arrayNode("redirect_on_normal")
                ->beforeNormalization()
                    ->ifTrue(function ($v) {
                            return isset($v['redirect_url']) && isset($v['redirect_route']);
                        })
                    ->thenInvalid('You cannot use both "redirect_url" and "redirect_route" at same time.')
                ->end()
                ->canBeDisabled()
                ->info(
                    'By enabling "redirect_on_normal" website will redirect from maintenance page if maintenance mode is disabled.'
                )
                ->addDefaultsIfNotSet()
                ->children()
                    ->scalarNode("redirect_url")
                        ->info("Application will redirect from maintenance page to this url if maintenance_mode is false. You can only set one of redirect_url or redirect_route")
                        ->defaultValue("/")
                    ->end()
                    ->scalarNode("redirect_route")->end()
                ->end()
            ->end();
    }
}
