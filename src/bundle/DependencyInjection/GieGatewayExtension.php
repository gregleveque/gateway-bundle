<?php

namespace Gie\GatewayBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\Yaml\Yaml;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class GieGatewayExtension extends Extension implements PrependExtensionInterface

{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('gie_gateway.routes', $config['routes']);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('cache.yml');
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        //$this->prependFileContent($container, 'cache.yml');

    }

    public function prependFileContent(ContainerBuilder $container, $file)
    {
        $configs = Yaml::parseFile(__DIR__ . '/../Resources/config/' . $file);
        foreach ($configs as $parameter => $config) {
            $container->prependExtensionConfig($parameter, $config);
        }
    }

}
