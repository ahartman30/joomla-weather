<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\Layout\LayoutHelper;

/** @var \Weather\Component\WeatherOpenData\Administrator\View\Products\HtmlView $this */

$displayData = [
  'textPrefix' => 'COM_WEATHEROPENDATA_PRODUCTS',
  'formURL'    => 'index.php?option=com_weatheropendata&view=products',
  'helpURL'    => '',
  'icon'       => 'icon-bookmark',
];

$displayData['createURL'] = 'index.php?option=com_weatheropendata&task=product.add';
echo LayoutHelper::render('joomla.content.emptystate', $displayData);
