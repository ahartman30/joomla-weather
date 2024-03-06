<?php

defined('_JEXEC') or die;

use Joomla\CMS\Extension\PluginInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Event\DispatcherInterface;
use Weather\Plugin\Content\WeatherMap\Extension\WeatherMap;

return new class () implements ServiceProviderInterface {

  /**
   * Registers the service provider with a DI container.
   *
   * @param   Container  $container  The DI container.
   *
   * @return  void
   *
   * @since   4.4.0
   */
  public function register(Container $container): void {
    $container->set(
      PluginInterface::class,
      function (Container $container) {
        $dispatcher = $container->get(DispatcherInterface::class);
        $plugin     = new WeatherMap(
          $dispatcher,
          (array) PluginHelper::getPlugin('content', 'weathermap')
        );
        $plugin->setApplication(Factory::getApplication());

        return $plugin;
      }
    );
  }
};
