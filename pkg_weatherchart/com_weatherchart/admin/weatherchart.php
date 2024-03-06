<?php
defined('_JEXEC') or die('Restricted access');

$controller = JControllerLegacy::getInstance('WeatherChart');
$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));
$controller->redirect();
