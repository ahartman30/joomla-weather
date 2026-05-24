<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\View\Manual;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\Filesystem\Path;
use Weather\Component\WeatherOpenData\Administrator\Model\ChartModel;

\defined('_JEXEC') or die;

/**
 * View to show the manual.
 *
 * @since 1.2.1
 */
class HtmlView extends BaseHtmlView
{

  /**
   * Display the view
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   * @return  void
   * @throws  \Exception
   * @since 1.2.1
   */
  public function display($tpl = null): void
  {
    parent::display($tpl);
  }

}