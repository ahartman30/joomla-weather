<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\Controller;

use Joomla\CMS\MVC\Controller\AdminController;

\defined('_JEXEC') or die;

/**
 * Products list controller.
 *
 * @since 1.1.0
 */
class ProductsController extends AdminController
{

  /**
   * The prefix to use with controller messages.
   *
   * @var    string
   * @since 1.1.0
   */
  protected $text_prefix = 'COM_WEATHEROPENDATA_PRODUCTS';

  /**
   * Method to get a model object, loading it if required.
   *
   * @param   string  $name    The model name. Optional.
   * @param   string  $prefix  The class prefix. Optional.
   * @param   array   $config  Configuration array for model. Optional.
   *
   * @return  \Joomla\CMS\MVC\Model\BaseDatabaseModel  The model.
   * @since 1.1.0
   */
  public function getModel($name = 'Product', $prefix = 'Administrator', $config = ['ignore_request' => true])
  {
    return parent::getModel($name, $prefix, $config);
  }

}