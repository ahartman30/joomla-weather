<?php
defined('_JEXEC') or die('Restricted access');

class WeatherChartModelTemplates extends JModelList {

  /**
   * Constructor.
   *
   * @param   array  An optional associative array of configuration settings.
   *
   * @since 1.0.0
   */
  public function __construct($config = array()) {
    $config['filter_fields'] = array('name', 'file');
    parent::__construct($config);
  }


  /**
   * Method to build an SQL query to load the list data.
   *
   * @return  string  An SQL query
   * @since 1.0.0
   */
  protected function getListQuery() {
    $db    = JFactory::getDBO();
    $query = $db->getQuery(true);
    $query->select('id, name, file, timestamp, template');
    $query->from('#__weatherchart_templates');

    $search = $this->getState('filter.search');
    if (!empty($search)) {
      $search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
      $query->where('(name LIKE ' . $search . ')');
    }

    $query->order($db->escape($this->getState('list.ordering') . ' ' . $this->getState('list.direction')));

    return $query;
  }

  /**
   * Method to auto-populate the model state.
   *
   * This method should only be called once per instantiation and is designed
   * to be called on the first call to the getState() method unless the model
   * configuration flag to ignore the request is set.
   *
   * Note. Calling getState in this method will result in recursion.
   *
   * @param   string  $ordering   An optional ordering field.
   * @param   string  $direction  An optional direction (asc|desc).
   *
   * @return  void
   * @since 1.0.0
   */
  protected function populateState($ordering = null, $direction = null) {
    $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
    $this->setState('filter.search', $search);

    parent::populateState('name', 'asc');
  }
}
