<?php

namespace Weather\Plugin\Content\WeatherStation\Extension;

use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\String\StringHelper;
use Joomla\Filesystem\Path;
use RuntimeException;

// No direct access
defined('_JEXEC') or die;

class WeatherStation extends CMSPlugin {

  const CMD = "{WeatherStation}";
  const REGEXP_FILE = "/{File\s(?P<jsonFile>\w+\.json)}/i";
  const INDEX_DELIMITER = ":";
  const REGEXP_PLACEHOLDER = "/%(?P<valueName>\w+(" . self::INDEX_DELIMITER . "\d+)?)%/i";

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
    if (!$this->getApplication()->isClient('site')) return; // Skip processing in admin interface

    // Check if article will be processed.
    if (!$this->isStationOn($row->text)) return;

    // Get files
    preg_match_all(self::REGEXP_FILE, $row->text, $matches);
    $countMatches = count($matches[0]);
    if ($countMatches) {
      $files = array_unique($matches["jsonFile"]);
    }
    else {
      $this->error('Keine json Dateien angegeben!');
      $files = array();
    }

    // Load data.
    $path = $this->resolveCleanAbsolutePath($this->params->get('file'));
    $data = array();
    foreach ($files as $fileName) {
      $fileData = null;
      $file     = $path . '/' . $fileName;
      if (file_exists($file)) $fileData = $this->loadData($file);
      if ($fileData != null) {
        $data = array_merge($data, $fileData);
      }
      else {
        $this->error('Datei ' . $fileName . ' existiert nicht oder enthält keine Daten!');
      }
    }
    if (count($data) == 0) {
      $this->printErrorMessage();
    }

    // Remove plugin cmd.
    $text = str_replace(self::CMD, '', $row->text);
    $text = preg_replace(self::REGEXP_FILE, '', $text);

    // Find all matches of placeholders.
    preg_match_all(self::REGEXP_PLACEHOLDER, $text, $matches);
    $countMatches = count($matches[0]);
    if ($countMatches) {
      $valueNames = array_unique($matches["valueName"]);
      $row->text  = $this->process($text, $valueNames, $data);
    }
  }

  private function isStationOn(string $text): bool {
    return !(StringHelper::strpos($text, self::CMD) === false);
  }

  private function resolveCleanAbsolutePath(string $relativePath): string {
    $path = JPATH_BASE . '/' . $relativePath;
    $path = Path::clean($path);

    return realpath($path);
  }

  private function loadData(string $file): mixed {
    $rawData = file_get_contents($file);
    $rawData = mb_convert_encoding($rawData, 'UTF-8', 'ISO-8859-1');

    return json_decode($rawData, true);
  }

  public function printErrorMessage(): void {
    $this->getApplication()->enqueueMessage($this->params->get('errormsg'), CMSApplicationInterface::MSG_NOTICE);
  }

  private function process(string $text, array $valueNames, array $data): string {
    foreach ($valueNames as $valueName) {
      $nameWithIndex = explode(self::INDEX_DELIMITER, $valueName);
      $name          = $nameWithIndex[0];
      $index         = count($nameWithIndex) > 1 ? $nameWithIndex[1] : null;
      $value         = array_key_exists($name, $data) ? $data[$name] : null;
      if (is_array($value) && is_numeric($index) && (count($value) > $index)) $value = $value[$index];
      if ($value === null || is_array($value)) $value = $this->params->get('errorplaceholder');
      $text = str_replace('%' . $valueName . '%', $value, $text);
    }

    return $text;
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