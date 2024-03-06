<?php
defined('_JEXEC') or die('Restricted Access');
?>
<?php foreach($this->items as $i => $item): ?>
  <tr class="row<?php echo $i % 2; ?>">
    <td>
      <?php echo JHtml::_('grid.id', $i, $item->id); ?>
    </td>
    <td>
      <a href="<?php echo JRoute::_('index.php?option=com_weatheropendata&task=product.edit&id='.(int) $item->id); ?>">
        <?php echo $item->name; ?>
      </a>
    </td>
    <td>
      <?php echo JText::_('COM_WEATHEROPENDATA_PRODUCT_FORMAT_'.strtoupper($item->product)); ?>
    </td>
    <td>
      <?php echo $item->cache_minutes; ?>
    </td>
    <td>
      <?php echo $item->protocol; ?>
    </td>
    <td>
      <?php echo $item->file; ?>
    </td>
  </tr>
<?php endforeach; ?>
