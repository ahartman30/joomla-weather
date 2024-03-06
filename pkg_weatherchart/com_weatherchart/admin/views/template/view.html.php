<?php

use Joomla\Filesystem\Path;

defined('_JEXEC') or die('Restricted access');

class WeatherChartViewTemplate extends JViewLegacy {

  protected $form;
  protected $item;


  /**
   * display method of product view
   * @return void
   * @since 1.0.0
   */
  public function display($tpl = null): void {

    // get the Data
    $this->form = $this->get('Form');
    $this->item = $this->get('Item');

    // Check for errors.
    if (count($errors = $this->get('Errors'))) {
      throw new Exception(implode("\n", $errors), 500);
    }

    $this->addToolBar();
    $this->setFileListDirectory();

    parent::display($tpl);
  }

  private function setFileListDirectory() {
    $path = JComponentHelper::getParams('com_weatherchart')->get('datapath');
    $path = JPATH_SITE . DIRECTORY_SEPARATOR . $path;
    $path = Path::clean($path);
    $path = realpath($path);
    $this->form->setFieldAttribute('file', 'directory', $path);
  }

  private function addToolBar() {
    JFactory::getApplication()->input->set('hidemainmenu', true);
    $isNew = $this->item->id == 0;
    JToolBarHelper::title($isNew ? JText::_('COM_WEATHERCHART_TEMPLATE_NEW') : JText::_('COM_WEATHERCHART_TEMPLATE_EDIT'), 'template');

    JToolBarHelper::apply('template.apply', 'JTOOLBAR_APPLY');
    JToolBarHelper::save('template.save', 'JTOOLBAR_SAVE');
    JToolBarHelper::cancel('template.cancel', 'JTOOLBAR_CLOSE');
  }

}
