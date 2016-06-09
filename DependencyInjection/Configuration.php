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
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('erfans_maintenance');

        /** ArrayNodeDefinition $rootNode */
        $rootNode
            ->beforeNormalization()
                ->ifTrue(function ($v) {
                    return isset($v['maintenance_route']) && isset($v['maintenance_url']);
                })
                ->thenInvalid('You cannot use both "maintenance_route" and "maintenance_url" at same time.')
            ->end()
            ->children()
                ->booleanNode("enabled")->defaultFalse()->end()
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
        $this->addAuthorizedUsersSection($rootNode);
        $this->addAuthorizedAreaSection($rootNode);
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

    private function addAuthorizedUsersSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode("authorized_users")
                ->info(
                    "While maintenance mode is enabled it is still possible to allow some users to visit the website based on users' roles or usernames or their IPs."
                )
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode("roles")
                        ->prototype('scalar')->end()
                        ->defaultValue(["ROLE_ADMIN", "ROLE_SUPER_ADMIN"])
                    ->end()
                    ->arrayNode("usernames")->prototype('scalar')->end()->end()
                    ->arrayNode("ip")->prototype('scalar')->end()->end()
                ->end()
            ->end();
    }

    private function addAuthorizedAreaSection(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode("authorized_areas")
                ->info("You may like exclude some pages from maintenance mode. Here you can define their paths or routes.")
                ->addDefaultsIfNotSet()
                ->children()
                    ->arrayNode("paths")
                        ->addDefaultChildrenIfNoneSet()
                        ->prototype('scalar')->defaultValue("/login")->end()
                    ->end()
                    ->arrayNode("routes")->prototype('scalar')->end()->end()
                ->end()
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
                ->treatFalseLike(['enabled' => false])
                ->treatTrueLike(['enabled' => true])
                ->treatNullLike(['enabled' => false])
                ->info(
                    'By enabling "redirect_on_normal" website will redirect from maintenance page if maintenance mode is disabled.'
                )
                ->addDefaultsIfNotSet()
                ->children()
                    ->booleanNode("enabled")->defaultTrue()->end()
                    ->scalarNode("redirect_url")
                        ->info("Application will redirect from maintenance page to this url if maintenance_mode is false. You can only set on of redirect_url or redirect_route")
                        ->defaultValue("/")
                    ->end()
                    ->scalarNode("redirect_route")->end()
                ->end()
            ->end();
    }
}
