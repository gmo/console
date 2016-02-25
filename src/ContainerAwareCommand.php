<?php

namespace GMO\Console;

use GMO\DependencyInjection\Container;
use Pimple;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @property Pimple $container
 */
class ContainerAwareCommand extends Command
{
    /** @var Pimple|null */
    private $container;

    /** @return Pimple */
    public function getContainer()
    {
        if ($this->container === null) {
            $this->container = $this->getDefaultContainer();
        }

        return $this->container;
    }

    /**
     * Returns $container[$name]
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getService($name)
    {
        return $this->getContainer()->offsetGet($name);
    }

    /**
     * Gets the application instance for this command.
     *
     * @param Pimple $container
     */
    public function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * Called if no container is given to the command
     *
     * @return Container
     */
    protected function getDefaultContainer()
    {
        return new Container();
    }

    /**
     * Calls an existing command
     *
     * @param OutputInterface $output
     * @param string          $name A command name or a command alias
     * @param array           $args
     *
     * @return int The command exit code
     * @throws \Exception
     */
    protected function callCommand(OutputInterface $output, $name, $args = array())
    {
        $args = array_merge(array('command' => $name), $args);
        $app = $this->getApplication();

        return $app->doRun(new ArrayInput($args), $output);
    }

    /**
     * Magic getter for $container property to support editor code completion.
     *
     * @param string $name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if ($name === 'container') {
            return $this->getContainer();
        }
        throw new \BadMethodCallException();
    }
}
