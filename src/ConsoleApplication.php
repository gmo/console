<?php
namespace GMO\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

class ConsoleApplication extends Application {

	public function add(Command $command) {
		if ($command instanceof ContainerAwareCommand) {
			$command->setContainer($this->container);
		}
		return parent::add($command);
	}

	/**
	 * Constructor.
	 *
	 * @param string       $name      The name of the application
	 * @param string       $version   The version of the application
	 * @param \Pimple|null $container The dependency container
	 * @api
	 */
	public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN', \Pimple $container = null) {
		$this->container = $container;
		parent::__construct($name, $version);
	}

	private $container;
}
