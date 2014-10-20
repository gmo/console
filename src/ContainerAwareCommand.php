<?php
namespace GMO\Console;

use GMO\DependencyInjection\Container;
use Pimple;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ContainerAwareCommand extends Command {

	private $container;

	/** @return Pimple */
	public function getContainer() {
		if ($this->container === null) {
			throw new \LogicException('The container cannot be retrieved as the instance is not yet set.');
		}
		return $this->container;
	}

	/**
	 * Gets the application instance for this command.
	 * @param Pimple $container
	 */
	public function setContainer($container) {
		$this->container = $container;
	}

	protected function execute(InputInterface $input, OutputInterface $output) {
		if (!$this->container) {
			$this->container = $this->getDefaultContainer(new Container());
		}
	}

	/**
	 * Called if no container is given to the command
	 * @param Container $container
	 * @return Container
	 */
	protected function getDefaultContainer(Container $container) {
		return $container;
	}

	/**
	 * Calls an existing command
	 * @param OutputInterface $output
	 * @param string          $name A command name or a command alias
	 * @param array           $args
	 * @return int The command exit code
	 * @throws \Exception
	 */
	protected function callCommand(OutputInterface $output, $name, $args = array()) {
		$args['command'] = $name;
		$input = new ArrayInput($args);
		$command = $this->getApplication()->find($name);
		if ($command instanceof ContainerAwareCommand) {
			$command->setContainer($this->getContainer());
		}
		return $command->run($input, $output);
	}
}
