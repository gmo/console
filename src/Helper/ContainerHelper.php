<?php
namespace GMO\Console\Helper;

use Symfony\Component\Console\Helper\Helper;

/**
 * The ContainerHelper class exposes a Pimple container to application/commands
 */
class ContainerHelper extends Helper
{
	protected $container;

	public function __construct(\Pimple $container) {
		$this->container = $container;
	}

	public function getContainer() {
		return $this->container;
	}

	public function getService($name) {
		return $this->container[$name];
	}

	public function getName() {
		return 'container';
	}
}
