<?php
defined('_JEXEC') or die('Restricted access');

// Needed for deletion

class WeatherOpenDataControllerProducts extends JControllerAdmin {

  public function getModel($name = 'Product', $prefix = 'WeatherOpenDataModel', $config = []) {
    $model = parent::getModel($name, $prefix, array('ignore_request' => true));

    return $model;
  }
}
