<?php

namespace Weather\Plugin\Content\WeatherChart\Extension;

use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Filesystem\Path;
use Joomla\String\StringHelper;
use RuntimeException;

defined('_JEXEC') or die;

class WeatherChart extends CMSPlugin {

  const HIGHCHARTS_VERSION = "5.0.14";
  const CMD = 'WeatherChart';
  const MEDIA_DIR = 'media/weatherchart';

  private string $cmdPattern;
  private string $dataDir;
  private string $mediaDir;
  private string $cacheDir;
  private string $cacheDirHttp;
  private string $themeVersion;

  private array $matches;


  /**
   * This is the first stage in preparing content for output and is the most common point for content orientated
   * plugins to do their work. Since the article and related parameters are passed by reference, event handlers can
   * modify them prior to display.
   *
   * @param   string  $context  The context of the content being passed to the plugin.
   * @param   mixed   $row      An object with a "text" property
   * @param   mixed   $params   Additional parameters. See {@see PlgContentContent()}.
   * @param   int     $page     Optional page number. Unused. Defaults to zero.
   *
   * @return  void
   * @since 1.0.0
   */
  public function onContentPrepare(string $context, mixed $row, mixed $params, int $page = 0): void {

    // simple performance check to determine if to proceed
    if (!$this->getApplication()->isClient('site')) return; // Skip processing in admin interface
    if (StringHelper::strpos($row->text, self::CMD) === false) return;

    $this->init($row->text);
    if (!$this->hasMatches()) return;

    $this->loadHighCharts();
    $row->text = $this->process($row->text);
  }

  private function init(string $text): void {
    $this->cmdPattern   = '/{' . self::CMD . '\s(?P<template>\w+);(?P<width>\d+);(?P<height>\d+)(;(?P<link>.+))?}/i';
    $this->dataDir      = ComponentHelper::getParams('com_weatherchart')->get('datapath');
    $this->themeVersion = ComponentHelper::getParams('com_weatherchart')->get('themeVersion');
    $this->dataDir      = $this->resolveCleanAbsolutePath($this->dataDir);
    $this->mediaDir     = self::MEDIA_DIR;
    $this->cacheDirHttp = $this->mediaDir . "/cache";
    $this->cacheDir     = Path::clean(JPATH_BASE . "/" . self::MEDIA_DIR . "/cache");
    $this->parseCmd($text);
  }

  private function resolveCleanAbsolutePath(string $relativePath): string {
    $path = JPATH_BASE . '/' . $relativePath;
    $path = Path::clean($path);

    return realpath($path);
  }

  private function parseCmd(string $text): void {
    preg_match_all($this->cmdPattern, $text, $matches);
    $count         = count($matches[0]);
    $this->matches = array();
    for ($i = 0; $i < $count; $i++) {
      $this->matches[$i]["template"] = $matches["template"][$i];
      $this->matches[$i]["cmd"]      = $matches[0][$i];
      $this->matches[$i]["height"]   = $matches["height"][$i];
      $this->matches[$i]["width"]    = $matches["width"][$i];
      $this->matches[$i]["link"]     = $matches["link"][$i];
    }
  }

  private function hasMatches(): bool {
    return (count($this->matches) > 0);
  }

  private function loadHighCharts(): void {
    $document = Factory::getDocument();
    $document->addScript($this->mediaDir . "/Highcharts/highcharts.js", array("version" => self::HIGHCHARTS_VERSION), array());
    $document->addScript($this->mediaDir . "/Highcharts/theme_" . $this->themeVersion . ".js", array("version" => self::HIGHCHARTS_VERSION), array());
  }

  private function process(string $text): string {
    foreach ($this->matches as $match) {
      $templateName = $match["template"];
      $width        = $match["width"];
      $height       = $match["height"];
      $link         = $match["link"];

      $chartData = $this->loadData($templateName);
      if ($chartData == null) {
        $this->error("Weather Chart: Chart '" . $templateName . "' existiert nicht.");
        continue;
      }

      $dataFile = $this->dataDir . DIRECTORY_SEPARATOR . $chartData['file'] . ".json";
      if (!is_readable($dataFile)) {
        $this->error("Weather Chart: Daten '" . $dataFile . "' nicht lesbar.");
        continue;
      }

      $containerId = "chart_" . $templateName;
      $cacheFile   = $this->getNewestCacheFile($containerId);
      if (!$this->cacheFileUptodate($cacheFile, $chartData['timestamp'], $dataFile)) {
        $chart       = $chartData['template'];
        $dataContent = file_get_contents($dataFile);
        $dataContent = mb_convert_encoding($dataContent, 'UTF-8', 'ISO-8859-1');
        $chart       = str_replace("%DATA%", $dataContent, $chart);
        $chart       = str_replace("%CONTAINER_ID%", $containerId, $chart);
        $chart       = str_replace("json_data", "data_" . $containerId, $chart);
        $this->cleanCache($containerId);
        $cacheFile = $this->createCache($containerId, $chart);
      }

      $cacheFileName = basename($cacheFile);
      $cachFileUrl   = $this->cacheDirHttp . "/" . $cacheFileName;
      Factory::getDocument()->addScript($cachFileUrl);

      $includeWithLink = strlen($link) > 0;
      if ($includeWithLink) {
        $chartContainer = $this->getContainerHtmlForBox($containerId, $width, $height, $link);
      }
      else {
        $chartContainer = $this->getContainerHtml($containerId, $width, $height);
      }
      $text = str_replace($match["cmd"], $chartContainer, $text);
    }

    return $text;
  }

  /**
   * Queries the data for the given template from the database.
   *
   * @param   string  $templateName  The template to load the data for.
   *
   * @return array|null With associative result or null.
   * @since 1.0.0
   */
  private function loadData(string $templateName): ?array {
    $db    = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('file, timestamp, template');
    $query->from('#__weatherchart_templates');
    $query->where("name=" . $query->quote($templateName, true));
    $db->setQuery($query);
    $result = $db->loadAssoc();

    return $result;
  }

  private function cacheFileUptodate(?string $cacheFile, string $timeTemplate, string $dataFile): bool {
    if (!isset($cacheFile) || !file_exists($cacheFile)) return false;
    $timeCacheFile = filemtime($cacheFile);
    $timeData      = filemtime($dataFile);

    return (is_readable($cacheFile) && $timeCacheFile >= $timeData && $timeCacheFile >= $timeTemplate);
  }

  private function createCache(string $chartName, string $content): ?string {
    $file   = $this->getNewCacheFile($chartName);
    $handle = fopen($file, "w");
    if (!$handle) {
      $this->error("Cache Datei '" . $file . "' kann nicht zum Schreiben geöffnet werden.");

      return null;
    }
    if (!fwrite($handle, $content)) {
      $this->error("Fehler beim Schreiben in Cache Datei '" . $file . "'.");

      return null;
    }
    fclose($handle);

    return $file;
  }

  private function getNewestCacheFile(string $chartName): ?string {
    $files = $this->getCacheFiles($chartName);
    if (count($files) == 0) return null;
    rsort($files);

    return $files[0];
  }

  private function cleanCache(string $chartName): void {
    $files = $this->getCacheFiles($chartName);
    foreach ($files as $file) {
      unlink($file);
    }
  }

  private function getCacheFiles(string $chartName): array {
    $files = array();
    if ($handle = opendir($this->cacheDir)) {
      while (($file = readdir($handle)) !== false) {
        $filename = basename($file);
        if (preg_match('/' . $chartName . '_\d+/', $filename)) {
          $files[] = $this->cacheDir . "/" . $file;
        }
      }
      closedir($handle);
    }

    return $files;
  }

  private function getNewCacheFile(string $chartName): string {
    return $this->cacheDir . "/" . $chartName . "_" . time() . ".js";
  }

  private function getContainerHtml(string $containerId, string $width, string $height): string {
    $html = '<div id="' . $containerId . '" style="width: ' . $width . 'px; height: ' . $height . 'px;"></div>';

    return $html;
  }

  private function getContainerHtmlForBox(string $containerId, string $width, string $height, string $link): string {
    $linkId   = $containerId . '_link';
    $inlineId = $containerId . '_inline';

    $ext   = substr($link, strlen($link) - 4);
    $isImg = ($ext == ".jpg" || $ext == ".png" || $ext == ".gif");
    if ($isImg) {
      $imgFile     = $this->mediaDir . "/" . $link;
      $linkContent = '<img alt="' . $link . '" src="' . $imgFile . '" width="100%"/>';
    }
    else {
      $linkContent = $link;
    }

    $html = '<a id="' . $linkId . '" href="#' . $inlineId . '">' . $linkContent . '</a>';
    $html .= '<div style="display: none">';
    $html .= '<div id="' . $inlineId . '" style="background-color: #9F9F9F">';
    $html .= '<div id="' . $containerId . '" style="width: ' . $width . 'px; height: ' . $height . 'px; margin: 0;"></div>';
    $html .= '</div></div>';

    return $html;
  }

  private function error(string $msg): void {
    if ($this->loggedIn()) {
      $this->getApplication()->enqueueMessage($msg, CMSApplicationInterface::MSG_ERROR);
    }
  }

  private function loggedIn(): bool {
    try {
      $user = Factory::getApplication()->getIdentity();
    }
    catch (Exception $e) {
      throw new RuntimeException("Getting Application object failed.", $e);
    }
    if ($user->id) {
      return true;
    }

    return false;
  }

}
