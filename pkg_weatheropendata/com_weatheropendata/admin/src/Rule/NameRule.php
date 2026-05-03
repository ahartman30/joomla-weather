<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

namespace Weather\Component\WeatherOpenData\Administrator\Rule;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\CMS\Language\Text;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

/**
 * Server side name input validation.
 *
 * @since 1.1.0
 */
class NameRule extends FormRule {

  private const string ALLOWED_NAME_PATTERN = '/^[\w_-]+$/';

  /**
   * Method to test the value.
   *
   * @param   \SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
   * @param   mixed              $value    The form field value to validate.
   * @param   string             $group    The field name group control value. This acts as an array container for the field.
   *                                       For example if the field has name="foo" and the group value is set to "bar" then the
   *                                       full field name would end up being "bar[foo]".
   * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
   * @param   ?Form              $form     The form object for which the field is being tested.
   *
   * @return  boolean  True if the value is valid, false otherwise.
   *
   * @since 1.1.0
   * @throws  \UnexpectedValueException if rule is invalid.
   */
  public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null) {

    // Server side name input validation.
    $nameAllowed = preg_match(self::ALLOWED_NAME_PATTERN, $value);
    if (!$nameAllowed) {
      throw new \UnexpectedValueException('Name allowed: a-z, A-Z, 0-9, _ and - ');
    }

    // Check duplicate.
    if ($form->getName() === "com_weatheropendata.product") {
      $tableName = "#__weatheropendata_products";
    } elseif ($form->getName() === "com_weatheropendata.chart") {
      $tableName = "#__weatheropendata_charts";
    } else {
      throw new \UnexpectedValueException("Invalid form name '". $form->getName() ."' for name rule validation.");
    }

    $id = $input->get('id', 0);
    $db = Factory::getContainer()->get(DatabaseInterface::class);
    $query = $db->getQuery(true);
    $query->select("COUNT(*)");
    $query->from($tableName);
    $query->where("name = " . $db->quote($value));
    $query->where($db->quoteName('id') . ' <> ' . (int) $id);
    $db->setQuery($query);
    $duplicate = (bool) $db->loadResult();

    return !$duplicate;
  }
}
