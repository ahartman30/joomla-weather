<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license         GNU GPL v3 or later
 */

defined('_JEXEC') || die();

use Joomla\CMS\Application\AdministratorApplication;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Installer\InstallerScriptInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Table\Extension;
use Joomla\Database\DatabaseInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;
use Joomla\Filesystem\Path;


return new class () implements ServiceProviderInterface {
  public function register(Container $container)
  {
    $container->set(
      InstallerScriptInterface::class,
      new class (
        $container->get(AdministratorApplication::class),
        $container->get(DatabaseInterface::class)
      ) implements InstallerScriptInterface {
        private AdministratorApplication $app;
        private DatabaseInterface $db;

        public function __construct(AdministratorApplication $app, DatabaseInterface $db)
        {
          $this->app = $app;
          $this->db  = $db;
        }

        public function install(InstallerAdapter $adapter): bool
        {
          return true;
        }

        public function update(InstallerAdapter $adapter): bool
        {
          if (version_compare($this->getCurrentInstalledVersion(), '1.2.0', '<')) {
            $this->deleteOldMediaFolder();
            $this->initNewParams();
            $this->migrateCommands();
            $this->app->enqueueMessage(Text::_('COM_WEATHEROPENDATA_UPDATE_INSERTTEXT_PLUGIN'), CMSApplicationInterface::MSG_INFO);
          }
          $this->clearMediaContentFolder();
          return true;
        }

        public function uninstall(InstallerAdapter $adapter): bool
        {
          return true;
        }

        public function preflight(string $type, InstallerAdapter $adapter): bool
        {
          return true;
        }

        public function postflight(string $type, InstallerAdapter $adapter): bool
        {
          return true;
        }

        private function getCurrentInstalledVersion(): ?string {
          $thisComponent = ComponentHelper::getComponent('com_weatheropendata');
          $table = new Extension($this->db);
          $table->load($thisComponent->id);
          $manifestCache = json_decode($table->manifest_cache, true);
          return $manifestCache['version'] ?? null;
        }

        private function deleteOldMediaFolder(): void
        {
          $oldMediaFolder = Path::clean(JPATH_ROOT . '/media/weatheropendata/');
          if (Folder::exists($oldMediaFolder)) {
            Folder::delete($oldMediaFolder);
            $this->app->enqueueMessage(Text::_('COM_WEATHEROPENDATA_UPDATE_MEDIAFOLDER'), CMSApplicationInterface::MSG_NOTICE);
          }
        }

        private function initNewParams(): void {
          $thisComponent = ComponentHelper::getComponent('com_weatheropendata');
          $paramsIterator = $thisComponent->getParams()->getIterator();
          while ($paramsIterator->valid()) {
            $thisComponentParams['params'][$paramsIterator->key()] = $paramsIterator->current();
            $paramsIterator->next();
          }

          // Set new params on update.
          if ($thisComponent->getParams()->get('datapath') === null) {
            $thisComponentParams['params']['datapath'] = './media/com_weatheropendata/chart/json';
            $toStore = true;
          }
          if ($thisComponent->getParams()->get('themeVersion') === null) {
            $thisComponentParams['params']['themeVersion'] = '1';
            $toStore = true;
          }
          if ($thisComponent->getParams()->get('insertcontentDataPath') === null) {
            $thisComponentParams['params']['insertcontentDataPath'] = '../Daten';
            $toStore = true;
          }

          // Get chart params from old component and copy highcharts files.
          if (ComponentHelper::isInstalled('com_weatherchart')) {
            $oldComponentParams = ComponentHelper::getParams('com_weatherchart');
            $thisComponentParams['params']['datapath'] = $oldComponentParams->get('datapath');
            $thisComponentParams['params']['themeVersion'] = $oldComponentParams->get('themeVersion');
            $this->app->enqueueMessage(Text::_('COM_WEATHEROPENDATA_UPDATE_CHARTS_COMPONENT'), CMSApplicationInterface::MSG_INFO);
            $toStore = true;

            $oldHighchartsFolder = Path::clean(JPATH_ROOT . '/media/weatherchart/Highcharts/');
            $highchartsFiles = Folder::files(path: $oldHighchartsFolder, full: true, excludeFilter: ['^\..*', '.*~', "index.html"]);
            $newHighchartsFolder = Path::clean(JPATH_ROOT . '/media/com_weatheropendata/js/highcharts/');
            foreach ( $highchartsFiles as $highchartsFile) {
              File::copy($highchartsFile, $newHighchartsFolder . '/' . basename($highchartsFile));
            }
          }

          if ($toStore === true) {
            $table = new Extension($this->db);
            $table->load($thisComponent->id);
            $table->bind($thisComponentParams);
            $table->store();
          }
        }

        private function migrateCommands(): void {
          $cmdUpdates = array(
            'opendata_show' => 'opendata:product-show',
            'opendata_get' => 'opendata:product-get',
            'opendata_load' => 'opendata:product-load',
            'WeatherChart' => 'opendata:chart',
            'insert_text' => 'opendata:insert',
          );
          foreach ($cmdUpdates as $oldCmd => $newCmd) {
            $query = $this->db->createQuery();
            $query
              ->update($this->db->quoteName('#__content'))
              ->set($this->db->quoteName('introtext') . ' = REPLACE(' . $this->db->quoteName('introtext') . ', :search, :replace)')
              ->bind(':search', $oldCmd)
              ->bind(':replace', $newCmd);
            $this->db->setQuery($query);
            $this->db->execute();

            $query = $this->db->createQuery();
            $query
              ->update($this->db->quoteName('#__modules'))
              ->set($this->db->quoteName('content') . ' = REPLACE(' . $this->db->quoteName('content') . ', :search, :replace)')
              ->where($this->db->quoteName('module') . ' = ' . $this->db->quote('mod_custom'))
              ->bind(':search', $oldCmd)
              ->bind(':replace', $newCmd);
            $this->db->setQuery($query);
            $this->db->execute();
          }
        }

        private function clearMediaContentFolder(): void
        {
          $cacheFolder = Path::clean(JPATH_ROOT . '/media/com_weatheropendata/content/');
          $cachedFiles = Folder::files(path: $cacheFolder, full: true, excludeFilter: ['^\..*', '.*~', "index.html"]);
          File::delete($cachedFiles);
        }

      }
    );
  }
};
