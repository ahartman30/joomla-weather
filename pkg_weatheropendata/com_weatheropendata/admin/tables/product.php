<?php
defined('_JEXEC') or die('Restricted access');

class WeatherOpenDataTableProduct extends JTable {

  /**
   * Constructor
   *
   * @param   object  $db  Database connector object
   *
   * @since 1.0.0
   */
  function __construct(&$db) {
    parent::__construct('#__weatheropendata_products', 'id', $db);
  }

}
