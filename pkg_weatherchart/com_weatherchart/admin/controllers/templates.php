<?php
defined('_JEXEC') or die('Restricted access');

// Needed for deletion

class WeatherChartControllerTemplates extends JControllerAdmin {

  public function getModel($name = 'Template', $prefix = 'WeatherChartModel', $config = []) {
    $model = parent::getModel($name, $prefix, array('ignore_request' => true));

    return $model;
  }
}
