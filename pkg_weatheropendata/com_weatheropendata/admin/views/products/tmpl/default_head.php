<?php
defined('_JEXEC') or die('Restricted Access');
?>
<tr>
  <th width="20">
    <?php echo JHtml::_('grid.checkall'); ?>
  </th>
  <th width="300">
    <?php echo JHTML::_('grid.sort', 'COM_WEATHEROPENDATA_HEADING_NAME', 'name', $this->sortDirection, $this->sortOrder); ?>
  </th>
  <th width="70">
    <?php echo JHTML::_('grid.sort', 'COM_WEATHEROPENDATA_HEADING_PRODUCT', 'product', $this->sortDirection, $this->sortOrder); ?>
  </th>
  <th width="70">
    <?php echo JHTML::_('grid.sort', 'COM_WEATHEROPENDATA_HEADING_CACHE', 'cache_minutes', $this->sortDirection, $this->sortOrder); ?>
  </th>
  <th width="70">
    <?php echo JHTML::_('grid.sort', 'COM_WEATHEROPENDATA_HEADING_PROTOCOL', 'protocol', $this->sortDirection, $this->sortOrder); ?>
  </th>
  <th>
    <?php echo JHTML::_('grid.sort', 'COM_WEATHEROPENDATA_HEADING_FILE', 'file', $this->sortDirection, $this->sortOrder); ?>
  </th>
</tr>
