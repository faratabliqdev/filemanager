<?php

namespace Adsign\FileManagerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Ali Kazemi <Developer@Azrael.ir> , <Kazemi@Adsign.co>
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
        $rootNode = $treeBuilder->root('fara_data_file_manager');

        $rootNode
            ->children()
                ->scalarNode('web_dir')
                    ->defaultValue('web')
                ->end()
                ->arrayNode('conf')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('dir')->end()
                            ->enumNode('type')->values(['file', 'image', 'media'])->end()
                            ->booleanNode('tree')->end()
                            ->enumNode('view')->values(['thumbnail', 'list'])->defaultValue('list')->end()
                            ->scalarNode('regex')->end()
                            ->scalarNode('service')->end()
                            ->scalarNode('accept')->end()
                            ->arrayNode('upload')
                                ->children()
                                    ->integerNode('min_file_size')->end()
                                    ->integerNode('max_file_size')->end()
                                    ->integerNode('max_width')->end()
                                    ->integerNode('max_height')->end()
                                    ->integerNode('min_width')->end()
                                    ->integerNode('min_height')->end()
                                    ->integerNode('image_library')->end()
                                    ->arrayNode('image_versions')
                                        ->prototype('array')
                                            ->children()
                                                ->booleanNode('auto_orient')->end()
                                                ->booleanNode('crop')->end()
                                                ->integerNode('max_width')->end()
                                                ->integerNode('max_height')->end()
                                            ->end()
                                        ->end()
                                    ->end()
                                    ->booleanNode('override')->defaultValue(false)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
