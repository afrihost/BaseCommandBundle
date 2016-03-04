<?php

namespace Afrihost\BaseCommandBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class AfrihostBaseCommandExtension extends Extension
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

        if (isset($config['php']['memory_limit'])) {
            $container->setParameter('afrihost_base_command.php.memory_limit', $config['php']['memory_limit']);
        }

        $container->setParameter('afrihost_base_command.logger.handler_strategies.file_stream.file_extension', $config['logger']['handler_strategies']['file_stream']['file_extension']);
        $container->setParameter('afrihost_base_command.locking.enabled', $config['locking']['enabled']);
        $container->setParameter('afrihost_base_command.locking.lock_file_folder', $config['locking']['lock_file_folder']);

        foreach ($config['logger']['handler_strategies'] as $strategyName => $strategyConfig) {
            $container->setParameter('afrihost_base_command.logger.handler_strategies.' . $strategyName . '.enabled', true); // By default all strategies are enabled at this time
            foreach ($strategyConfig as $strategyConfigKey => $strategyConfigDetail) {
                $container->setParameter(
                    'afrihost_base_command.logger.handler_strategies.' . $strategyName . '.' . $strategyConfigKey,
                    $config['logger']['handler_strategies'][$strategyName][$strategyConfigKey]
                );
            }
        }
    }
}
