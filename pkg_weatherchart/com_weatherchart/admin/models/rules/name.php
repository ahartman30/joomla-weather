<?php

defined('_JEXEC') or die('Restricted access');

class JFormRuleName extends JFormRule {

	/**
	 * Method to test the chart name for uniqueness.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed              $value    The form field value to validate.
	 * @param   string             $group    The field name group control value. This acts as as an array container for the field.
	 *                                       For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                       full field name would end up being "bar[foo]".
	 * @param   ?Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   ?Form              $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   1.6
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, $input = null, $form = null): bool {
		// Get the database object and a new query object.
		$db    = JFactory::getDBO();
		$query = $db->getQuery(true);

		// Build the query.
		$query->select('COUNT(*)');
		$query->from('#__weatherchart_templates');
		$query->where('name = ' . $db->quote($value));

		// Get record id
		$id = $input->get('id', 0);
		$query->where($db->quoteName('id') . ' <> ' . (int) $id);

		// Set and query the database.
		$db->setQuery($query);
		$duplicate = (bool) $db->loadResult();

		return !$duplicate;
	}
}
