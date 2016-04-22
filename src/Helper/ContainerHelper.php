<?php

namespace GMO\Console\Helper;

use ArrayAccess;
use Symfony\Component\Console\Helper\Helper;

/**
 * The ContainerHelper class exposes a Pimple container to application/commands
 */
class ContainerHelper extends Helper
{
    /** @var ArrayAccess */
    protected $container;

    /**
     * Constructor.
     *
     * @param ArrayAccess $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Returns container.
     * 
     * @return ArrayAccess
     */
    public function getContainer()
    {
        return $this->container;
    }

    /**
     * Returns service from container by name.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getService($name)
    {
        return $this->container[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'container';
    }
}
