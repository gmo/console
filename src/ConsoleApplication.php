<?php
namespace GMO\Console;

use Symfony\Component\Console\Application;
use Symfony\Component\EventDispatcher\EventDispatcher;

class ConsoleApplication extends Application {

	/**
	 * Constructor.
	 *
	 * @param string       $name      The name of the application
	 * @param string       $version   The version of the application
	 * @param \Pimple|null $container The dependency container
	 * @api
	 */
	public function __construct($name = 'UNKNOWN', $version = 'UNKNOWN', \Pimple $container = null) {
		parent::__construct($name, $version);
		if ($container) {
			$dispatcher = new EventDispatcher();
			$dispatcher->addSubscriber(new ContainerAwareCommandSubscriber($container));
			$this->setDispatcher($dispatcher);
		}
	}
}
