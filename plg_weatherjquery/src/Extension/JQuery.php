<?php

namespace Weather\Plugin\System\JQuery\Extension;

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;

defined('_JEXEC') or die;


class JQuery extends CMSPlugin {

  const JQUERY_VERSION = "1.12.4";
  const JQUERY_MIGRATE_VERSION = "1.4.1";
  const MEDIA_DIR = 'media/weatherjquery/jquery';

  public function onAfterInitialise(): void {
    if (!$this->getApplication()->isClient('site')) return; // Skip processing in admin interface

    $document = Factory::getDocument();
    $document->addScript(self::MEDIA_DIR . "/jquery.min.js", array("version" => self::JQUERY_VERSION), array());
    $document->addScript(self::MEDIA_DIR . "/jquery-migrate.min.js", array("version" => self::JQUERY_MIGRATE_VERSION), array());
    $document->addScript(self::MEDIA_DIR . "/jquery-noConflict.js", array("version" => self::JQUERY_VERSION), array());
  }
}
