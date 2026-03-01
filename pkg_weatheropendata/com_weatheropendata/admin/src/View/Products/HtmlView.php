<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\View\Products;

use Joomla\CMS\Form\Form;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Pagination\Pagination;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Weather\Component\WeatherOpenData\Administrator\Model\ProductsModel;

\defined('_JEXEC') or die;

/**
 * View class for a list of products.
 *
 * @since 1.1.0
 */
class HtmlView extends BaseHtmlView
{

  /**
   * The search tools form
   *
   * @var    Form
   * @since 1.1.0
   */
  public $filterForm;

  /**
   * The active search filters
   *
   * @var    array
   * @since 1.1.0
   */
  public $activeFilters = [];

  /**
   * An array of items
   *
   * @var    array
   * @since 1.1.0
   */
  protected $items = [];

  /**
   * The pagination object
   *
   * @var    Pagination
   * @since 1.1.0
   */
  protected $pagination;

  /**
   * The model state
   *
   * @var    \Joomla\Registry\Registry
   * @since 1.1.0
   */
  protected $state;

  /**
   * Is this view an Empty State
   *
   * @var  boolean
   * @since 1.1.0
   */
  private $isEmptyState = false;

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
    /** @var ProductsModel $model */
    $model = $this->getModel();

    $this->items         = $model->getItems();
    $this->pagination    = $model->getPagination();
    $this->state         = $model->getState();
    $this->filterForm    = $model->getFilterForm();
    $this->activeFilters = $model->getActiveFilters();

    if (!\count($this->items) && $this->isEmptyState = $model->getIsEmptyState()) {
      $this->setLayout('emptystate');
    }
    $this->addToolbar();

    parent::display($tpl);
  }

  /**
   * Returns the user readable product.
   *
   * @param $product string The raw database type of the product.
   * @since 1.1.0
   */
  public function translateProduct($product): string
  {
    return Text::_('COM_WEATHEROPENDATA_PRODUCT_FORMAT_'.strtoupper($product));
  }

  /**
   * Add the page title and toolbar.
   *
   * @return  void
   * @since 1.1.0
   */
  protected function addToolbar(): void
  {
    $canDo   = ContentHelper::getActions('com_weatheropendata');
    $toolbar = $this->getDocument()->getToolbar();

    ToolbarHelper::title(Text::_('COM_WEATHEROPENDATA_PRODUCTS'), 'icon-list-2');

    if ($canDo->get('core.create')) {
      $toolbar->addNew('product.add');
    }

    if (!$this->isEmptyState && $canDo->get('core.delete')) {
      $toolbar->delete('products.delete', 'JTOOLBAR_DELETE_FROM_TRASH')
        ->message('JGLOBAL_CONFIRM_DELETE')
        ->listCheck(true);
    }

    if ($canDo->get('core.admin') || $canDo->get('core.options')) {
      $toolbar->preferences('com_weatheropendata');
    }
  }

}