<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license         GNU GPL v3 or later
 */

namespace Weather\Plugin\Content\OpenData\Extension\Processor\Strategies;

use DateTimeZone;
use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\WebAsset\WebAssetManager;
use Joomla\Database\DatabaseInterface;
use Weather\Plugin\Content\OpenData\Extension\OpenDataPlugin;
use Weather\Plugin\Content\OpenData\Extension\Processor\OpenDataProcessorException;
use Weather\Plugin\Content\OpenData\Extension\Processor\OpenDataProcessorStrategy;

defined('_JEXEC') or die;

/**
 * Responsible for processing a chart command.
 *
 * @since       1.2.0
 */
class ChartProcessor implements OpenDataProcessorStrategy {

  public const string CMD = 'chart';

  private string $dataDir;
  private string $cacheDir;
  private string $cacheDirUriLocation;
  private string $themeVersion;
  private bool $addUrlTimestamp;
  private DatabaseInterface $db;
  private CMSApplicationInterface $app;

  /**
   * Constructor.
   *
   * @param DatabaseInterface $db
   * @param CMSApplicationInterface $app
   * @param string $relativeJsonDataDir Path to the JSON data files folder from the component settings.
   * @param string $relativeMediaContentDir Path to the media content folder, relative to the site root.
   * @param string $themeVersion The theme version from the component settings.
   * @param bool $addUrlTimestamp If true, the chart url will be suffixed with a timestamp.
   */
  public function __construct(
    DatabaseInterface       $db,
    CMSApplicationInterface $app,
    string                  $relativeJsonDataDir,
    string                  $relativeMediaContentPath,
    string                  $themeVersion,
    bool                    $addUrlTimestamp)
  {
    $this->db = $db;
    $this->app = $app;
    $this->dataDir = $relativeJsonDataDir ?? '';
    $this->dataDir = OpenDataPlugin::resolveCleanAbsolutePath($this->dataDir);;
    $this->themeVersion = $themeVersion ?? '1';
    $this->cacheDirUriLocation = $relativeMediaContentPath;
    $this->cacheDir = OpenDataPlugin::resolveCleanAbsolutePath($relativeMediaContentPath);
    $this->addUrlTimestamp = $addUrlTimestamp ?? true;
    $this->initHighcharts();
  }

  private function initHighcharts(): void {
    /** @var WebAssetManager $webAssetManager */
    $webAssetManager = $this->app->getDocument()->getWebAssetManager();
    $webAssetManager->getRegistry()->addExtensionRegistryFile('com_weatheropendata');
    $webAssetManager->useScript('com_weatheropendata.highcharts');
    $webAssetManager->useScript('com_weatheropendata.highcharts.theme_' . $this->themeVersion);
  }

  public function execute(array $parameters, ?string $subCommand): string {
    if (count($parameters) < 1) throw new OpenDataProcessorException('Missing template name.');
    if (count($parameters) > 3) throw new OpenDataProcessorException('Too much product parameters.');
    $templateName = $parameters[0];
    if (count($parameters) >= 2) $height = $parameters[1];
    if (count($parameters) >= 3) $width = $parameters[2];
    if (empty($width)) $width = "100%";
    if (empty($height)) $height = "100%";
    if (is_numeric($width)) $width .= "px"; // backward compatibility
    if (is_numeric($height)) $height .= "px";

    try {
      return $this->process($templateName, $height, $width);
    } catch (OpenDataProcessorException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new OpenDataProcessorException(sprintf('Error processing template "%s": %s', $templateName, $e->getMessage()), $e);
    }
  }

  private function process(string $templateName, string $height, string $width): string {
    $chartData = $this->loadData($templateName);
    if ($chartData == null) throw new OpenDataProcessorException(sprintf('Chart template "%s" does not exist.', $templateName));
    $dataFile = $this->dataDir . DIRECTORY_SEPARATOR . $chartData['file'] . ".json";
    if (!is_readable($dataFile)) throw new OpenDataProcessorException(sprintf('Chart data file "%s" for template "%s" is not readable.', $dataFile, $templateName));
    $containerId = "chart_" . $templateName;
    $cacheFile = $this->getCacheFile($containerId);
    $templateTimestamp = $chartData['timestamp'];
    $chartTemplate = $chartData['template'];
    if (!$this->isCacheFileUptodate($cacheFile, $templateTimestamp, $dataFile)) {
      $chart = $this->createChart($chartTemplate, $dataFile, $containerId);
      $this->updateCacheFile($cacheFile, $chart);
    }
    $cacheFileName = basename($cacheFile);
    $cachFileUri = $this->cacheDirUriLocation . "/" . $cacheFileName;

    /** @var WebAssetManager $webAssetManager */
    $webAssetManager = $this->app->getDocument()->getWebAssetManager();
    if ($this->addUrlTimestamp) {
      $cacheFileTimestamp = new \DateTime('@' . filemtime($cacheFile), new DateTimeZone('UTC'))->format(\DateTime::ATOM);
      $webAssetManager->registerAndUseScript('com_weatheropendata.chart_' . $containerId, $cachFileUri, ['version' => 'lastmodified=' . $cacheFileTimestamp]);
    } else {
      $webAssetManager->registerAndUseScript('com_weatheropendata.chart_' . $containerId, $cachFileUri, ['version' => '']);
    }
    return $this->getContainerHtml($containerId, $width, $height);
  }

  /**
   * Queries the data for the given template from the database.
   *
   * @param   string  $templateName  The template to load the data for.
   *
   * @return array|null With associative result or null on failure.
   * @since 1.0.0
   */
  private function loadData(string $templateName): ?array {
    $query = $this->db->createQuery()
      ->select($this->db->quoteName(['file', 'timestamp', 'template']))
      ->from($this->db->quoteName('#__weatheropendata_charts'))
      ->where($this->db->quoteName('name') . ' = :name')
      ->bind(':name', $templateName);
    $this->db->setQuery($query);
    return $this->db->loadAssoc();
  }

  private function getCacheFile(string $filename): string {
    return $this->cacheDir . DIRECTORY_SEPARATOR . $filename . ".js";
  }

  private function isCacheFileUptodate(string $cacheFile, string $timeTemplate, string $dataFile): bool {
    if (!file_exists($cacheFile)) return false;
    $timeCacheFile = filemtime($cacheFile);
    $timeData = filemtime($dataFile);
    return (is_readable($cacheFile) && $timeCacheFile >= $timeData && $timeCacheFile >= $timeTemplate);
  }

  public function createChart(string $template, string $dataFile, string $containerId): string {
    $dataContent = file_get_contents($dataFile);
    $dataContent = mb_convert_encoding($dataContent, 'UTF-8', 'ISO-8859-1');
    $chart = str_replace("%DATA%", $dataContent, $template);
    $chart = str_replace("%CONTAINER_ID%", $containerId, $chart);
    $chart = str_replace("json_data", "data_" . $containerId, $chart);
    return $chart;
  }

  private function updateCacheFile(string $cacheFile, string $content): void {
    if (file_exists($cacheFile)) unlink($cacheFile);
    $handle = fopen($cacheFile, "w");
    if (!$handle) throw new OpenDataProcessorException(sprintf('Error creating cache cacheFile "%s".', $cacheFile));
    if (!fwrite($handle, $content)) throw new OpenDataProcessorException(sprintf('Error writing to cache cacheFile "%s".', $cacheFile));
    fclose($handle);
  }

  private function getContainerHtml(string $containerId, string $width, string $height): string {
    return sprintf('<div id="%s" style="width: %s; height: %s;"></div>', $containerId, $width, $height);
  }

  public function finish(): void { }

}
