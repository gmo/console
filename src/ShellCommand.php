<?php

namespace GMO\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Shell;

class ShellCommand extends Command
{
    protected function configure()
    {
        parent::configure();
        $this
            ->setName('shell')
            ->setDescription('Run application in shell mode')
        ;
    }

    public function isEnabled()
    {
        return class_exists('\Symfony\Component\Console\Shell');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $shell = new Shell($this->getApplication());
        $shell->run();
    }
}
