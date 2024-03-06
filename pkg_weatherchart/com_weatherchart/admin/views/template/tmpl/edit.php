<?php
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

?>

<script type="text/javascript">
	Joomla.submitbutton = function(task) {
		if (task === 'template.cancel' || document.formvalidator.isValid(document.getElementById('template-form'))) {
			Joomla.submitform(task, document.getElementById('template-form'));
		}
	}
</script>

<form action="<?php echo JRoute::_('index.php?option=com_weatherchart&layout=edit&id='.(int) $this->item->id); ?>" method="post" name="adminForm" id="template-form" class="form-validate">
  <div class="form-horizontal row-fluid form-horizontal-desktop">
    <?php echo $this->form->renderField('id'); ?>
    <?php echo $this->form->renderField('timestamp'); ?>
    <?php echo $this->form->renderField('name'); ?>
    <?php echo $this->form->renderField('file'); ?>
  </div>
  <div>
    <?php echo $this->form->renderField('template'); ?>
    <h3>Platzhalter:</h3>
    <ul>
    <li>%DATA% = Platzhalter für JSON-Daten.</li>
    <li>%CONTAINER_ID% = Platzhalter für automatisch generierte div-Container ID.</li>
    </ul>
  </div>
  <div>
    <input type="hidden" name="task" value="" />
    <?php echo JHtml::_('form.token'); ?>
  </div>
</form>
