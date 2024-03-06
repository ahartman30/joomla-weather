<?php

namespace Weather\Plugin\System\FancyBox\Extension;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Registry\Registry;

defined('_JEXEC') or die('Restricted access');

class FancyBox extends CMSPlugin {

  const FANCYBOX_CLASS = "fancybox";
  const MEDIA_DIR = 'media/weatherfancybox/fancybox';

  private string $transition;
  private string $margin;
  private string $padding;
  private string $autoScale;
  private string $cyclic;
  private string $mediaDir;


  public function onAfterDispatch(): void {
    if (!$this->getApplication()->isClient('site')) return; // Skip processing in admin interface

    $this->init($this->params);

    $document = Factory::getDocument();
    $this->initHeader($document);
    $this->addFancyboxScript($document);
  }

  private function init(Registry $params): void {
    $this->transition = $params->get('transition');
    $this->margin     = $params->get('margin');
    $this->padding    = $params->get('padding');
    $this->autoScale  = $params->get('autoScale');
    $this->cyclic     = $params->get('cyclic');
    $this->mediaDir   = self::MEDIA_DIR;
  }

  private function initHeader(Document $document): void {
    $document->addScript($this->mediaDir . "/jquery.mousewheel-3.0.6.pack.js", array(), array("type" => "module"));
    $document->addScript($this->mediaDir . "/jquery.fancybox-1.3.4.pack.js", array(), array("type" => "module"));
    $document->addScript($this->mediaDir . "/jquery.easing-1.3.pack.js",array(), array("type" => "module"));
    $document->addStyleSheet($this->mediaDir . "/jquery.fancybox-1.3.4.css");
  }

  private function addFancyboxScript(Document $document): void {
    $script   = array();
    $script[] = "jqfb = jQuery.noConflict();";
    $script[] = "jqfb(document).ready(function() {";
    $script[] = "jqfb('a." . self::FANCYBOX_CLASS . "').fancybox({";
    $script[] = "'transitionIn' : '" . $this->transition . "',";
    $script[] = "'transitionOut' : '" . $this->transition . "',";
    $script[] = "'cyclic' : " . ($this->cyclic ? 'true' : 'false') . ",";
    $script[] = "'margin' : " . $this->margin . ",";
    $script[] = "'padding' : " . $this->padding . ",";
    $script[] = "'autoScale' : " . ($this->autoScale ? 'true' : 'false');
    $script[] = "});";
    $script[] = "});";

    $document->addScriptDeclaration(implode("\n", $script));
  }

}
