<?php

defined('_JEXEC') || die();

use Joomla\CMS\Installer\Adapter\PackageAdapter;
use Joomla\CMS\Installer\InstallerScript;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;

class Pkg_WeatherChartInstallerScript extends InstallerScript {

  public function preflight($type, $parent) {
    if (!parent::preflight($type, $parent)) {
      return false;
    }

    // Do not run on uninstall.
    if ($type === 'uninstall') {
      return true;
    }

    // Clear file cache on update.
    if ($type === 'update') {
      $cacheFolder = Path::clean(JPATH_ROOT . "/media/weatherchart/cache/");
      $cachedFiles = Folder::files(path: $cacheFolder, full: true, excludeFilter: ['^\..*', '.*~', "index.html"]);
      File::delete($cachedFiles);
    }

    return true;
  }

}