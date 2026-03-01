<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\Table;

use Joomla\CMS\Table\Table;
use Joomla\CMS\Table\TableInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Event\DispatcherInterface;

\defined('_JEXEC') or die;

/**
 * Product table.
 *
 * @since 1.1.0
 */
class ProductTable extends Table implements TableInterface
{

  /**
   * Indicates that columns fully support the NULL value in the database
   *
   * @var    boolean
   * @since 1.1.0
   */
  protected $_supportNullValue = true;

  /**
   * Constructor
   *
   * @param   DatabaseInterface     $db          Database connector object
   * @param   ?DispatcherInterface  $dispatcher  Event dispatcher for this table
   *
   * @since 1.1.0
   */
  public function __construct(DatabaseInterface $db, ?DispatcherInterface $dispatcher = null)
  {
    parent::__construct('#__weatheropendata_products', 'id', $db, $dispatcher);
  }

}