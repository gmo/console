<?php

namespace GMO\Console;

use Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;

/**
 * Base Application that adds:
 *   - Auto versioning from git or composer
 *   - Support for ContainerAwareCommand and ContainerHelper
 *   - Added completion and shell commands
 */
class ConsoleApplication extends Application
{
    /** @var \Pimple|\Pimple\Container|null */
    private $container;
    /** @var string|null */
    private $projectDir;

    /**
     * Constructor.
     *
     * @param string           $name      The name of the application
     * @param string           $version   The version of the application
     * @param \Pimple|\Pimple\Container|null $container The dependency container
     */
    public function __construct($name = 'UNKNOWN', $version = null, $container = null)
    {
        if ($container) {
            // Needed so default commands get container if they are container aware.
            $this->container = $container;
        }

        parent::__construct($name, $version);

        if ($container) {
            // Needs to be after parent constructor
            $this->getHelperSet()->set(new Helper\ContainerHelper($container));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function add(Command $command)
    {
        if ($this->container && $command instanceof ContainerAwareCommand) {
            $command->setContainer($this->container);
        }

        return parent::add($command);
    }

    /**
     * {@inheritdoc}
     */
    public function getVersion()
    {
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
     * {@inheritdoc}
     */
    protected function getDefaultCommands()
    {
        $commands = parent::getDefaultCommands();
        if (class_exists('\Stecman\Component\Symfony\Console\BashCompletion\CompletionCommand')) {
            $commands[] = new CompletionCommand();
        }
        $commands[] = new ShellCommand();
        $commands[] = new PimpleDumpCommand();

        return $commands;
    }

    /**
     * Gets the package name. Used in determining version.
     *
     * @return string|null
     */
    protected function getPackageName()
    {
        return null;
    }

    /**
     * Gets the project directory. Used in determining version.
     *
     * @return string|null
     */
    public function getProjectDirectory()
    {
        return $this->projectDir;
    }

    /**
     * Set the project directory. Used in determining version.
     *
     * @param string $dir
     *
     * @return $this
     */
    public function setProjectDirectory($dir)
    {
        $this->projectDir = $dir;

        return $this;
    }

    /**
     * Reads the composer lock file based on the project directory
     * and parses the version for the specified package name.
     *
     * @param string $packageName
     * @param string $projectDir
     *
     * @return null|string
     */
    protected static function findPackageVersion($packageName, $projectDir)
    {
        if ($packageName === null || $projectDir === null) {
            return null;
        }
        $composerFile = file_exists("$projectDir/vendor") ?
            "$projectDir/composer.lock" : "$projectDir/../../../composer.lock";
        if (!file_exists($composerFile)) {
            return null;
        }
        $composer = json_decode(file_get_contents($composerFile), true);

        $packages = array_filter($composer['packages'], function ($package) use ($packageName) {
            return $package['name'] === $packageName;
        });
        if (!$packages) {
            return null;
        }
        $package = reset($packages);

        if (!isset($package['version'])) {
            return null;
        }

        $version = ltrim($package['version'], 'v');

        return $version;
    }

    /**
     * Returns the current git branch name and revision or null if git repo cannot be found.
     *
     * @param string $projectDir
     *
     * @return null|string
     */
    protected static function findGitVersion($projectDir)
    {
        $branch = static::revParse('--abbrev-ref HEAD', $projectDir);

        if (empty($branch)) {
            return null;
        }

        return $branch . ' ' . static::revParse('--short HEAD', $projectDir);
    }

    /**
     * Runs `git rev-parse` in the $projectDir with the given $args.
     *
     * @param string $args
     * @param string $projectDir
     *
     * @return string
     */
    protected static function revParse($args, $projectDir)
    {
        return trim(shell_exec("cd $projectDir && git rev-parse $args 2> /dev/null"));
    }
}
