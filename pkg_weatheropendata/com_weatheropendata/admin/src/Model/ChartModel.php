<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormFactoryInterface;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Table\Table;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Path;

\defined('_JEXEC') or die;

/**
 * Chart model.
 *
 * @since 1.2.0
 */
class ChartModel extends AdminModel
{

  /**
   * The type alias for this content type.
   *
   * @var    string
   * @since 1.2.0
   */
  public $typeAlias = 'com_weatheropendata.chart';

  /**
   * Path to the JSON files.
   *
   * @var   string
   * @since 1.2.0
   */
  private $jsonFilesPath;


  public function __construct($config = [], ?MVCFactoryInterface $factory = null, ?FormFactoryInterface $formFactory = null)
  {
    parent::__construct($config, $factory, $formFactory);
    $path = ComponentHelper::getParams('com_weatheropendata')->get('datapath');
    $path = JPATH_SITE . DIRECTORY_SEPARATOR . $path;
    $path = Path::clean($path);
    $path = realpath($path);
    $this->jsonFilesPath = $path;
  }


  /**
   * Method to get the record form.
   *
   * @param   array    $data      Data for the form.
   * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
   *
   * @return  \Joomla\CMS\Form\Form|boolean  A Form object on success, false on failure
   *
   * @since 1.2.0
   */
  public function getForm($data = [], $loadData = true)
  {
    $form = $this->loadForm('com_weatheropendata.chart', 'chart', ['control' => 'jform', 'load_data' => $loadData]);
    if (empty($form)) return false;
    $form->setFieldAttribute('file', 'directory', $this->jsonFilesPath);
    return $form;
  }

  /**
   * Method to get the data that should be injected in the form.
   *
   * @return  mixed  The data for the form.
   *
   * @since 1.2.0
   */
  protected function loadFormData()
  {
    // Check the session for previously entered form data.
    $data = Factory::getApplication()->getUserState('com_weatheropendata.edit.chart.data', []);
    if (empty($data)) {
      $data = $this->getItem();
    }

    $this->preprocessData('com_weatheropendata.chart', $data);
    $this->createDefaultDataFile($data->file);
    return $data;
  }

  /**
   * Creates a default data file if it does not exist.
   * Else the file wont't be shown in the file list selected.
   *
   * @since 1.2.0
   */
  private function createDefaultDataFile($filename): void
  {
    $jsonFile = $this->jsonFilesPath . DIRECTORY_SEPARATOR . $filename . '.json';
    if (!File::exists($jsonFile))
    {
      File::write($jsonFile, '{}');
    }
  }

  /**
   * Prepare and sanitise the table prior to saving.
   *
   * @param   Table  $table  A Table object.
   *
   * @return  void
   *
   * @since 1.2.0
   */
  protected function prepareTable($table)
  {
    $table->name = htmlspecialchars_decode($table->name, ENT_QUOTES);
    $table->timestamp = time();
  }

}