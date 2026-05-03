<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \Weather\Component\WeatherOpenData\Administrator\View\Charts\HtmlView $this */

$displayData = [
  'textPrefix' => 'COM_WEATHEROPENDATA_CHARTS',
  'formURL'    => 'index.php?option=com_weatheropendata&view=charts',
  'helpURL'    => '',
  'icon'       => 'icon-bookmark',
];

$displayData['createURL'] = 'index.php?option=com_weatheropendata&task=chart.add';
echo LayoutHelper::render('joomla.content.emptystate', $displayData);
