<?php
namespace GMO\Console;

use GMO\Common\Collections\ArrayCollection;
use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

class ConsoleApplication extends Application {

	public function add(Command $command) {
		if ($command instanceof ContainerAwareCommand) {
			$command->setContainer($this->container);
		}
		return parent::add($command);
	}

	public function getVersion() {
		if (!parent::getVersion() && $this->getPackageName() && $this->getProjectDirectory()) {
			$version = $this->findPackageVersion($this->getPackageName(), $this->getProjectDirectory());
			$this->setVersion($version ?: 'development');
		}
		return parent::getVersion();
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

	protected function getDefaultCommands() {
		$commands = parent::getDefaultCommands();
		if (class_exists('\Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand')) {
			$commands[] = new CompletionCommand();
		}
		return $commands;
	}

	/**
	 * Gets the package name. Used in determining version.
	 * @return string|null
	 */
	protected function getPackageName() { return null; }

	/**
	 * Gets the project directory. Used in determining version.
	 * @return string|null
	 */
	protected function getProjectDirectory() { return null; }

	/**
	 * Reads the composer lock file based on the project directory
	 * and parses the version for the specified package name.
	 *
	 * @param string $packageName
	 * @param string $projectDir
	 * @return null|string
	 */
	protected static function findPackageVersion($packageName, $projectDir) {
		$composerFile = file_exists("$projectDir/vendor") ?
			"$projectDir/composer.lock" : "$projectDir/../../../composer.lock";
		if (!file_exists($composerFile)) {
			return null;
		}
		$composer = json_decode(file_get_contents($composerFile), true);

		$packages = ArrayCollection::create($composer['packages'])
			->filter(function($package) use ($packageName) {
				return $package['name'] === $packageName;
			});
		if ($packages->isEmpty()) {
			return null;
		}
		$package = ArrayCollection::create($packages->first());

		$version = ltrim($package->get('version'), 'v');
		return $version;
	}

	private $container;
}
