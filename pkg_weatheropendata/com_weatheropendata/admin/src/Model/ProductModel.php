<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\Model;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;

\defined('_JEXEC') or die;

/**
 * Product model.
 *
 * @since 1.1.0
 */
class ProductModel extends AdminModel
{

  /**
   * The type alias for this content type.
   *
   * @var    string
   * @since 1.1.0
   */
  public $typeAlias = 'com_weatheropendata.product';

  /**
   * Method to get the record form.
   *
   * @param   array    $data      Data for the form.
   * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
   *
   * @return  \Joomla\CMS\Form\Form|boolean  A Form object on success, false on failure
   *
   * @since 1.1.0
   */
  public function getForm($data = [], $loadData = true)
  {
    // Get the form.
    $form = $this->loadForm('com_weatheropendata.product', 'product', ['control' => 'jform', 'load_data' => $loadData]);

    if (empty($form)) {
      return false;
    }

    return $form;
  }

  /**
   * Method to get the data that should be injected in the form.
   *
   * @return  mixed  The data for the form.
   *
   * @since 1.1.0
   */
  protected function loadFormData()
  {
    // Check the session for previously entered form data.
    $data = Factory::getApplication()->getUserState('com_weatheropendata.edit.product.data', []);

    if (empty($data)) {
      $data = $this->getItem();
    }

    $this->preprocessData('com_weatheropendata.product', $data);

    return $data;
  }

  /**
   * Prepare and sanitise the table prior to saving.
   *
   * @param   Table  $table  A Table object.
   *
   * @return  void
   *
   * @since 1.1.0
   */
  protected function prepareTable($table)
  {
    $table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
  }

}