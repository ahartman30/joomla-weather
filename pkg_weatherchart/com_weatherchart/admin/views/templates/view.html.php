<?php
defined('_JEXEC') or die('Restricted access');

class WeatherChartViewTemplates extends JViewLegacy {

  protected $items;
  protected $pagination;
  protected $sortOrder;
  protected $sortDirection;
  protected $search;

  /**
   * display method
   * @return void
   * @since 1.0.0
   */
  function display($tpl = null): void {

    // Get data from the model
    $items      = $this->get('Items');
    $pagination = $this->get('Pagination');
    $state      = $this->get('State');

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      throw new Exception(implode("\n", $errors), 500);
    }

    // Assign data to the view
    $this->items         = $items;
    $this->pagination    = $pagination;
    $this->sortOrder     = $this->escape($state->get('list.ordering'));
    $this->sortDirection = $this->escape($state->get('list.direction'));
    $this->search        = $this->escape($state->get('filter.search'));

    // Set the toolbar
    $this->addToolBar();

    // Display the template
    parent::display($tpl);
  }

  /**
   * Setting the toolbar
   */
  protected function addToolBar() {
    JToolBarHelper::title(JText::_('COM_WEATHERCHART_TEMPLATES'), 'templates');
    JToolBarHelper::addNew('template.add', 'JTOOLBAR_NEW');
    JToolBarHelper::deleteList('', 'templates.delete', 'JTOOLBAR_DELETE');
    JToolBarHelper::divider();
    JToolBarHelper::preferences('com_weatherchart');
  }

}
