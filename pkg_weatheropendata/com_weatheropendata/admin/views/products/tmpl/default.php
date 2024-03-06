<?php

defined('_JEXEC') or die('Restricted Access');

JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');

?>
<form action="<?php echo JRoute::_('index.php?option=com_weatheropendata'); ?>" method="post" name="adminForm" id="adminForm">

	<div id="filter-bar" class="btn-toolbar">
		<div class="filter-search btn-group pull-left">
			<label for="filter_search" class="element-invisible"><?php echo JText::_('COM_CONTACT_FILTER_SEARCH_DESC');?></label>
			<input type="text" name="filter_search" id="filter_search" placeholder="<?php echo JText::_('JSEARCH_FILTER'); ?>" value="<?php echo $this->search; ?>" class="hasTooltip" title="<?php echo JHtml::tooltipText('COM_WEATHEROPENDATA_SEARCH_IN_NAME'); ?>" />
		</div>
		<div class="btn-group pull-left">
			<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>"><i class="icon-search"></i></button>
			<button type="button" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>" onclick="document.getElementById('filter_search').value='';this.form.submit();"><i class="icon-remove"></i></button>
		</div>
		<div class="btn-group pull-left">
			<label for="limit" class="element-invisible"><?php echo JText::_('JFIELD_PLG_SEARCH_SEARCHLIMIT_DESC');?></label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
	</div>
	<div class="clearfix"> </div>

	<table class="table table-striped">
	 <thead><?php echo $this->loadTemplate('head');?></thead>
	 <tfoot><?php echo $this->loadTemplate('foot');?></tfoot>
	 <tbody><?php echo $this->loadTemplate('body');?></tbody>
	</table>

	<div>
	 <input type="hidden" name="task" value="" />
	 <input type="hidden" name="boxchecked" value="0" />
	 <input type="hidden" name="filter_order" value="<?php echo $this->sortOrder; ?>" />
	 <input type="hidden" name="filter_order_Dir" value="<?php echo $this->sortDirection; ?>" />
	 <?php echo JHtml::_('form.token'); ?>
	</div>

</form>


