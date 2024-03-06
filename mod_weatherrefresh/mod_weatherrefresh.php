<?php

use Joomla\CMS\Factory;

defined('_JEXEC') or die('Restricted access');

$refreshTime = $params->get('refreshTime');
if (is_numeric($refreshTime) && $refreshTime > 0) {
  $refreshTime *= 60;
  $document = Factory::getApplication()->getDocument();
  $document->setMetaData("refresh", $refreshTime);
}
