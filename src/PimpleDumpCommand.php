<?php

namespace GMO\Console;

use Silex;
use Sorien\Provider\PimpleDumpProvider;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\Request;

class PimpleDumpCommand extends ContainerAwareCommand
{
    public function isEnabled()
    {
        return class_exists('\Sorien\Provider\PimpleDumpProvider');
    }

    protected function configure()
    {
        parent::configure();
        $this->setName('pimple:dump')
            ->setDescription('Dump the pimple container for the idea plugin')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->getContainer();
        if (!$app instanceof Silex\Application) {
            throw new \InvalidArgumentException('Container given needs to be an instance of Silex\Application');
        }

        $app['debug'] = true;

        $dumper = new PimpleDumpProvider();
        $app->register($dumper);
        $dumper->boot($app);

        $request = Request::create('/');
        $response = $app->handle($request);
        $app->terminate($request, $response);
    }
}
