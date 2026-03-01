<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\View\Product;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\GenericDataException;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Weather\Component\WeatherOpenData\Administrator\Model\ProductModel;

\defined('_JEXEC') or die;

/**
 * View to edit a product.
 *
 * @since 1.1.0
 */
class HtmlView extends BaseHtmlView
{
  /**
   * The Form object
   *
   * @var    Form
   * @since 1.1.0
   */
  protected $form;

  /**
   * The active item
   *
   * @var    object
   * @since 1.1.0
   */
  protected $item;

  /**
   * The model state
   *
   * @var    object
   * @since 1.1.0
   */
  protected $state;

  /**
   * Display the view
   *
   * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
   * @return  void
   * @throws  \Exception
   * @since 1.1.0
   */
  public function display($tpl = null): void
  {
    /** @var ProductModel $model */
    $model       = $this->getModel();
    $this->form  = $model->getForm();
    $this->item  = $model->getItem();
    $this->state = $model->getState();

    // Check for errors.
    if (\count($errors = $model->getErrors())) {
      throw new GenericDataException(implode("\n", $errors), 500);
    }

    $this->addToolbar();
    parent::display($tpl);
  }

  /**
   * Add the page title and toolbar.
   *
   * @return  void
   * @throws  \Exception
   * @since 1.1.0
   */
  protected function addToolbar(): void
  {
    Factory::getApplication()->getInput()->set('hidemainmenu', true);

    $isNew      = ($this->item->id == 0);
    $toolbar    = $this->getDocument()->getToolbar();
    $canDo      = ContentHelper::getActions('com_weatheropendata');

    ToolbarHelper::title($isNew ? Text::_('COM_WEATHEROPENDATA_PRODUCT_NEW_TOOLBAR') : Text::_('COM_WEATHEROPENDATA_PRODUCT_EDIT_TOOLBAR'), 'icon-list-2');

    if ($canDo->get('core.edit')) {
      $toolbar->apply('product.apply');
    }

    $saveGroup = $toolbar->dropdownButton('save-group');
    $saveGroup->configure(
      function (Toolbar $childBar) use ($canDo, $isNew) {
        if ($canDo->get('core.edit')) {
          $childBar->save('product.save');
          if ($canDo->get('core.create')) {
            $childBar->save2new('product.save2new');
          }
        }
        if (!$isNew && $canDo->get('core.create')) {
          $childBar->save2copy('product.save2copy');
        }
      }
    );

    if (empty($this->item->id)) {
      $toolbar->cancel('product.cancel', 'JTOOLBAR_CANCEL');
    } else {
      $toolbar->cancel('product.cancel');
    }

    $toolbar->inlineHelp();
  }

}