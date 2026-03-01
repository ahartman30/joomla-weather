<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\Controller;

use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Router\Route;

\defined('_JEXEC') or die;

/**
 * Opendata default display controller.
 *
 * @since 1.1.0
 */
class DisplayController extends BaseController
{

  /**
   * The default view.
   *
   * @var    string
   * @since 1.1.0
   */
  protected $default_view = 'products';

  /**
   * Method to display a view.
   *
   * @param   boolean  $cachable   If true, the view output will be cached
   * @param   array    $urlparams  An array of safe URL parameters and their variable types
   * @see     \Joomla\CMS\Filter\InputFilter::clean() for valid values.
   *
   * @return  BaseController|boolean  This object to support chaining.
   * @since 1.1.0
   */
  public function display($cachable = false, $urlparams = [])
  {
    $view   = $this->input->get('view', 'products');
    $layout = $this->input->get('layout', 'default');
    $id     = $this->input->getInt('id');

    // Do not allow direct call of edit form.
    if ($view === 'product' && $layout === 'edit' && !$this->checkEditId('com_weatheropendata.edit.product', $id)) {
      if (!\count($this->app->getMessageQueue())) {
        $this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id), 'error');
      }
      $this->setRedirect(Route::_('index.php?option=com_weatheropendata&view=products', false));
      return false;
    }

    return parent::display();
  }
}
