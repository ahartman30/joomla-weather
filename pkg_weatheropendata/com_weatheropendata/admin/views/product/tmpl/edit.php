<?php
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

?>

<script type="text/javascript">
    Joomla.submitbutton = function (task) {
        if (task === 'product.cancel' || document.formvalidator.isValid(document.getElementById('product-form'))) {
            Joomla.submitform(task, document.getElementById('product-form'));
        }
    }
</script>

<form action="<?php echo JRoute::_('index.php?option=com_weatheropendata&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="product-form" class="form-validate">
    <div class="form-horizontal row-fluid form-horizontal-desktop">
      <?php echo $this->form->renderField('id'); ?>
      <?php echo $this->form->renderField('name'); ?>
      <?php echo $this->form->renderField('protocol'); ?>
      <?php echo $this->form->renderField('file'); ?>
      <?php echo $this->form->renderField('product'); ?>
      <?php echo $this->form->renderField('cache_minutes'); ?>
    </div>
    <div>
        <input type="hidden" name="task" value=""/>
      <?php echo JHtml::_('form.token'); ?>
    </div>
</form>
