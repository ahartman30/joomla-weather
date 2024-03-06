<?php
defined('_JEXEC') or die('Restricted access');

class WeatherOpenDataViewProduct extends JViewLegacy {

  protected $form;
  protected $item;


  /**
   * Display method of product view.
   *
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

    parent::display($tpl);
  }

  private function addToolBar(): void {
    $isNew = $this->item->id == 0;
    JToolBarHelper::title($isNew ? JText::_('COM_WEATHEROPENDATA_PRODUCT_NEW') : JText::_('COM_WEATHEROPENDATA_PRODUCT_EDIT'), 'template');

    JToolBarHelper::apply('product.apply', 'JTOOLBAR_APPLY');
    JToolBarHelper::save('product.save', 'JTOOLBAR_SAVE');
    JToolBarHelper::save2copy('product.save2copy');
    JToolBarHelper::cancel('product.cancel', 'JTOOLBAR_CLOSE');
  }

}
