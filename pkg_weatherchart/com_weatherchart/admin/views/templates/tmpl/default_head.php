<?php
defined('_JEXEC') or die('Restricted Access');
?>
<tr>
  <th width="20">
    <?php echo JHtml::_('grid.checkall'); ?>
  </th>
  <th width="300">
    <?php echo JHTML::_('grid.sort', 'COM_WEATHERCHART_HEADING_NAME', 'name', $this->sortDirection, $this->sortOrder); ?>
  </th>
  <th width="100">
    <?php echo JHTML::_('grid.sort', 'COM_WEATHERCHART_HEADING_FILE', 'file', $this->sortDirection, $this->sortOrder); ?>
  </th>
</tr>
