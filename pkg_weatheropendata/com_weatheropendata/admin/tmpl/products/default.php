<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license     GNU GPL v3 or later
 */

defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

/** @var \Weather\Component\WeatherOpenData\Administrator\View\Products\HtmlView $this */

/** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('table.columns')
        ->useScript('multiselect');

$user      = $this->getCurrentUser();
$userId    = $user->id;
$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn  = $this->escape($this->state->get('list.direction'));

?>
<form action="<?php echo Route::_('index.php?option=com_weatheropendata&view=products'); ?>" method="post" name="adminForm" id="adminForm">
    <div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                // Search tools bar
                echo LayoutHelper::render('joomla.searchtools.default', ['view' => $this]);
                ?>
                <?php if (empty($this->items)) : ?>
                    <div class="alert alert-info">
                        <span class="icon-info-circle" aria-hidden="true"></span><span class="visually-hidden"><?php echo Text::_('INFO'); ?></span>
                        <?php echo Text::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
                    </div>
                <?php else : ?>
                    <table class="table" id="productList">
                        <caption class="visually-hidden">
                            <?php echo Text::_('COM_WEATHEROPENDATA_PRODUCTS_TABLE_CAPTION'); ?>,
                            <span id="orderedBy"><?php echo Text::_('JGLOBAL_SORTED_BY'); ?> </span>,
                            <span id="filteredBy"><?php echo Text::_('JGLOBAL_FILTERED_BY'); ?></span>
                        </caption>
                        <thead>
                        <tr>
                            <td class="w-1 text-center">
                                <?php echo HTMLHelper::_('grid.checkall'); ?>
                            </td>
                            <th scope="col">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_WEATHEROPENDATA_HEADING_NAME', 'a.name', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-15 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_WEATHEROPENDATA_HEADING_PRODUCT', 'a.product', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-5 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_WEATHEROPENDATA_HEADING_CACHE', 'a.cache_minutes', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="w-5 d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_WEATHEROPENDATA_HEADING_PROTOCOL', 'a.protocol', $listDirn, $listOrder); ?>
                            </th>
                            <th scope="col" class="d-none d-md-table-cell">
                                <?php echo HTMLHelper::_('searchtools.sort', 'COM_WEATHEROPENDATA_HEADING_FILE', 'a.file', $listDirn, $listOrder); ?>
                            </th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($this->items as $i => $item) :
                            $ordering  = ($listOrder == 'ordering');
                            ?>
                            <tr class="row<?php echo $i % 2; ?>">
                                <td class="text-center">
                                    <?php echo HTMLHelper::_('grid.id', $i, $item->id, false, 'cid', 'cb', $item->name); ?>
                                </td>
                                <th scope="row"> <?php //th row cannot be hidden ?>
                                    <a href="<?php echo Route::_('index.php?option=com_weatheropendata&task=product.edit&id=' . (int) $item->id); ?>"
                                       title="<?php echo Text::_('JACTION_EDIT'); ?> <?php echo $this->escape($item->name); ?>">
                                        <?php echo $this->escape($item->name); ?></a>
                                </th>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $this->translateProduct($item->product); ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $item->cache_minutes; ?>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <?php echo $item->protocol; ?>
                                </td>
                                <td class="small d-none d-md-table-cell">
                                    <?php echo $item->file; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php echo $this->pagination->getListFooter(); ?>
                <?php endif; ?>

                <input type="hidden" name="task" value="">
                <input type="hidden" name="boxchecked" value="0">
                <?php echo HTMLHelper::_('form.token'); ?>
            </div>
        </div>
    </div>
</form>
