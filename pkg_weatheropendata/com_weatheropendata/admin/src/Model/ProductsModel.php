<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\QueryInterface;

\defined('_JEXEC') or die;

/**
 * Methods supporting a list of product records.
 *
 * @since 1.1.0
 */
class ProductsModel extends ListModel
{

  /**
   * Constructor.
   *
   * @param   array                 $config   An optional associative array of configuration settings.
   * @param   ?MVCFactoryInterface  $factory  The factory.
   *
   * @since 1.1.0
   */
  public function __construct($config = [], ?MVCFactoryInterface $factory = null)
  {
    if (empty($config['filter_fields'])) {
      $config['filter_fields'] = [
        'name', 'a.name',
        'product', 'a.product',
        'cache_minutes', 'a.cache_minutes',
        'protocol', 'a.protocol',
        'file', 'a.file',
        'id', 'a.id'
      ];
    }
    parent::__construct($config, $factory);
  }

  /**
   * Method to auto-populate the model state.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @param   string  $ordering   An optional ordering field.
   * @param   string  $direction  An optional direction (asc|desc).
   *
   * @return  void
   * @since 1.1.0
   */
  protected function populateState($ordering = 'a.name', $direction = 'asc')
  {
    // Load the parameters.
    $this->setState('params', ComponentHelper::getParams('com_weatheropendata'));

    // List state information.
    parent::populateState($ordering, $direction);
  }

  /**
   * Method to get a store id based on model configuration state.
   *
   * This is necessary because the model is used by the component and
   * different modules that might need different sets of data or different
   * ordering requirements.
   *
   * @param   string  $id  A prefix for the store id.
   *
   * @return  string  A store id.
   * @since 1.1.0
   */
  protected function getStoreId($id = '')
  {
    return parent::getStoreId($id);
  }

  /**
   * Build an SQL query to load the list data.
   *
   * @return  QueryInterface
   * @since 1.1.0
   */
  protected function getListQuery()
  {
    // Create a new query object.
    $db    = $this->getDatabase();
    $query = $db->createQuery();

    // Select the required fields from the table.
    $query->select(
      $this->getState(
        'list.select',
        [
          $db->quoteName('a.id'),
          $db->quoteName('a.name'),
          $db->quoteName('a.protocol'),
          $db->quoteName('a.file'),
          $db->quoteName('a.product'),
          $db->quoteName('a.cache_minutes')
        ]
      )
    )
      ->from($db->quoteName('#__weatheropendata_products', 'a'));

    // Filter by search in name and file.
    if ($search = trim($this->getState('filter.search', ''))) {
      $search = '%' . str_replace(' ', '%', $search) . '%';
      $query
        ->where($db->quoteName('a.name') . ' LIKE :searchName OR ' . $db->quoteName('a.file') . ' LIKE :searchFile')
        ->bind([ ':searchName', ':searchFile' ], $search);
    }

    // Filter by product type.
    if ($productType = $this->getState('filter.product')) {
      $query
        ->where($db->quoteName('a.product') . ' = :type')
        ->bind(':type', $productType);
    }

    // Filter by protocol.
    if ($protocol = $this->getState('filter.protocol')) {
      $query
        ->where($db->quoteName('a.protocol') . ' = :protocol')
        ->bind(':protocol', $protocol);
    }

    // Add the list ordering clause.
    $query->order($db->quoteName($db->escape($this->getState('list.ordering', 'a.name'))) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

    return $query;
  }

}