<?php

namespace Arpon\Database\Capsule;

trait CapsuleManagerTrait
{
    /**
     * The current globally used instance.
     *
     * @var object
     */
    protected static $instance;

    /**
     * The container instance.
     *
     * @var \Arpon\Database\Container
     */
    protected $container;

    /**
     * Setup the IoC container instance.
     *
     * @param  \Arpon\Database\Container  $container
     * @return void
     */
    protected function setupContainer($container)
    {
        $this->container = $container;

        if (! $this->container->bound('config')) {
            $this->container->instance('config', []);
        }
    }

    /**
     * Make this capsule instance available globally via static methods.
     *
     * @return void
     */
    public function setAsGlobal()
    {
        static::$instance = $this;
    }

    /**
     * Get the IoC container instance.
     *
     * @return \Arpon\Database\Container
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Set the IoC container instance.
     *
     * @param  \Arpon\Database\Container  $container
     * @return void
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }
}