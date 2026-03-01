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

/** @var \Weather\Component\WeatherOpenData\Administrator\View\Product\HtmlView $this */

/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
$wa = $this->getDocument()->getWebAssetManager();
$wa->useScript('keepalive')
        ->useScript('form.validate');

?>

<form action="<?php echo Route::_('index.php?option=com_weatheropendata&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="product-form" aria-label="<?php echo Text::_('COM_WEATHEROPENDATA_PRODUCT_' . ((int) $this->item->id === 0 ? 'NEW' : 'EDIT'), true); ?>" class="form-validate">

    <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>

    <div class="main-card">
        <?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', ['active' => 'general', 'recall' => true, 'breakpoint' => 768]); ?>

        <?php echo HTMLHelper::_('uitab.addTab', 'myTab', 'general', empty($this->item->id) ? Text::_('COM_WEATHEROPENDATA_PRODUCT_NEW_TAB') : Text::_('COM_WEATHEROPENDATA_PRODUCT_EDIT_TAB')); ?>
        <div class="row">
            <div class="col-lg-5">
                <?php
                echo $this->form->renderField('protocol');
                echo $this->form->renderField('file');
                echo $this->form->renderField('product');
                echo $this->form->renderField('cache_minutes');
                ?>
            </div>
        </div>
        <?php echo HTMLHelper::_('uitab.endTab'); ?>

        <?php echo HTMLHelper::_('uitab.endTabSet'); ?>
    </div>

    <input type="hidden" name="task" value="">
    <?php echo HTMLHelper::_('form.token'); ?>
</form>
