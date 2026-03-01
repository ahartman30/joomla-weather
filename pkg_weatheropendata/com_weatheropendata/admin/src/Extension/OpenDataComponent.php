<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\Extension;

use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\HTML\HTMLRegistryAwareTrait;
use Psr\Container\ContainerInterface;

\defined('_JEXEC') or die;

/**
 * Component class for com_weatheropendata.
 *
 * @since 1.1.0
 */
class OpenDataComponent extends MVCComponent implements
  BootableExtensionInterface,
  RouterServiceInterface
{
  use HTMLRegistryAwareTrait;
  use RouterServiceTrait;

  /**
   * Booting the extension. This is the function to set up the environment of the extension like
   * registering new class loaders, etc.
   *
   * If required, some initial set up can be done from services of the container, eg.
   * registering HTML services.
   *
   * @param   ContainerInterface  $container  The container
   * @return  void
   * @since 1.1.0
   */
  public function boot(ContainerInterface $container)
  {
  }

}