<?php

namespace Weather\Plugin\Console\OpenData\Extension;

defined('_JEXEC') or die('Restricted access');

use Joomla\Application\ApplicationEvents;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Weather\Plugin\Console\OpenData\CliCommand\RunFetchProductCommand;
use Weather\Plugin\Console\OpenData\CliCommand\RunLoadCacheCommand;


class OpenDataConsolePlugin extends CMSPlugin implements SubscriberInterface {

  public static function getSubscribedEvents(): array {
    return [
      ApplicationEvents::BEFORE_EXECUTE => 'registerCommands',
    ];
  }

  public function registerCommands(): void {
    $app = Factory::getApplication();
    $app->addCommand(new RunLoadCacheCommand());
    $app->addCommand(new RunFetchProductCommand());
  }

}
