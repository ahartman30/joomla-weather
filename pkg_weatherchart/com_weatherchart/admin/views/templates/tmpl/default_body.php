<?php
defined('_JEXEC') or die('Restricted Access');
?>
<?php foreach($this->items as $i => $item): ?>
  <tr class="row<?php echo $i % 2; ?>">
    <td>
      <?php echo JHtml::_('grid.id', $i, $item->id); ?>
    </td>
    <td>
      <a href="<?php echo JRoute::_('index.php?option=com_weatherchart&task=template.edit&id='.(int) $item->id); ?>">
        <?php echo $item->name; ?>
      </a>
    </td>
    <td>
      <?php echo $item->file; ?>
    </td>
  </tr>
<?php endforeach; ?>
