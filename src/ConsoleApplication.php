<?php
namespace GMO\Console;

use GMO\Common\Collections\ArrayCollection;
use GMO\Console\Helper\ContainerHelper;
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
		if (parent::getVersion() === null && $this->getProjectDirectory()) {
			if ($this->getPackageName()) {
				$version = $this->findPackageVersion($this->getPackageName(), $this->getProjectDirectory());
			} else {
				$version = $this->findGitVersion($this->getProjectDirectory());
			}

			$this->setVersion($version ?: 'UNKNOWN');
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
	public function __construct($name = 'UNKNOWN', $version = null, \Pimple $container = null) {
		$this->container = $container;
		parent::__construct($name, $version);
		if ($container) {
			$this->getHelperSet()->set(new Helper\ContainerHelper($container));
		}
	}

	protected function getDefaultCommands() {
		$commands = parent::getDefaultCommands();
		if (class_exists('\Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand')) {
			$commands[] = new CompletionCommand();
		}
		$commands[] = new ShellCommand();

		return $commands;
	}

	protected function getDefaultHelperSet()
	{
		$helperSet = parent::getDefaultHelperSet();
		$helperSet->set(new ContainerHelper($this->container));
		return $helperSet;
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
		if ($packageName === null || $projectDir === null) {
			return null;
		}
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

	protected static function findGitVersion($projectDir) {
		$branch = static::revParse('--abbrev-ref HEAD', $projectDir);

		if (empty($branch)) {
			return null;
		}

		return $branch . ' ' . static::revParse('--short HEAD', $projectDir);
	}

	protected static function revParse($args, $projectDir) {
		return trim(shell_exec("cd $projectDir && git rev-parse $args 2> /dev/null"));
	}

	private $container;
}
