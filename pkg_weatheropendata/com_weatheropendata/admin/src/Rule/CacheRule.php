<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license         GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\Rule;

use Joomla\CMS\Form\FormRule;

defined('_JEXEC') or die('Restricted access');

/**
 * Server side cache input validation.
 *
 * @since       1.1.0
 */
class CacheRule extends FormRule
{
  protected $regex = '^[0-9]+$';

}
