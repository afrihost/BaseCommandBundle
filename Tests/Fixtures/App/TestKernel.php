<?php
namespace Afrihost\BaseCommandBundle\Tests\Fixtures\App;

use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\VarDumper\VarDumper;

/**
 * This kernel provides a minimal Symfony Application for tests that rely on the framework being bootstrapped. It is a
 * self-contained alternative to requiring a 'dummy application' for testing
 */
class TestKernel extends Kernel
{

    /**
     * Returns an array of bundles to register.
     *
     * @return \Symfony\Component\HttpKernel\Bundle\BundleInterface[] An array of bundle instances.
     */
    public function registerBundles()
    {
        return array(
            new \Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new \Afrihost\BaseCommandBundle\AfrihostBaseCommandBundle()
        );
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_test.yml');
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return __DIR__.'/cache/var/cache/'.$this->environment;
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return __DIR__.'/cache/var/log/'.$this->environment;
    }
}
