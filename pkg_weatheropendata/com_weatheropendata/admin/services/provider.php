<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterFactoryInterface;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\CMS\HTML\Registry;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Weather\Component\WeatherOpenData\Administrator\Extension\OpenDataComponent;

/**
 * The weather opendata service provider.
 */
return new class () implements ServiceProviderInterface {
  /**
   * Registers the service provider with a DI container.
   *
   * @param   Container  $container  The DI container.
   */
  public function register(Container $container)
  {
    $container->registerServiceProvider(new MVCFactory('\\Weather\\Component\\WeatherOpenData'));
    $container->registerServiceProvider(new ComponentDispatcherFactory('\\Weather\\Component\\WeatherOpenData'));
    $container->registerServiceProvider(new RouterFactory('\\Weather\\Component\\WeatherOpenData'));

    $container->set(
      ComponentInterface::class,
      function (Container $container) {
        $component = new OpenDataComponent($container->get(ComponentDispatcherFactoryInterface::class));
        $component->setRegistry($container->get(Registry::class));
        $component->setMVCFactory($container->get(MVCFactoryInterface::class));
        $component->setRouterFactory($container->get(RouterFactoryInterface::class));
        return $component;
      }
    );
  }
};