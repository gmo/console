<?php
namespace GMO\Console;

use Pimple;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ContainerAwareCommandSubscriber implements EventSubscriberInterface {

	private $pimple;

	public function __construct(Pimple $pimple) {
		$this->pimple = $pimple;
	}

	public function onCommand(ConsoleEvent $event) {
		$command = $event->getCommand();
		if ($command instanceof ContainerAwareCommand) {
			$command->setContainer($this->pimple);
		}
	}

	/** @inheritdoc */
	public static function getSubscribedEvents() {
		return array(
			ConsoleEvents::COMMAND => 'onCommand'
		);
	}
}
