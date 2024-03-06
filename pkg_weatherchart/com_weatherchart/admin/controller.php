<?php
defined('_JEXEC') or die('Restricted access');

/**
 * General Controller of the component
 * @since 1.0.0
 */
class WeatherChartController extends JControllerLegacy {

	/**
	 * display task
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function display($cachable = false, $urlparams = false) {

		// set default view if not set
		$input = JFactory::getApplication()->input;
		$input->set('view', $input->getCmd('view', 'Templates'));

		// call parent behavior
		parent::display($cachable);
	}
}
