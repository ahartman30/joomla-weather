<?php

namespace Weather\Plugin\Content\OpenData\Extension;

defined('_JEXEC') or die('Restricted access');

use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Filesystem\Path;
use Joomla\String\StringHelper;
use RuntimeException;

/**
 * Plugin for embedding opendata content.
 *
 * @package     Weather\Plugin\Content\OpenData\Extension
 *
 * @since       1.0.0
 */
class OpenData extends CMSPlugin {

  const CMD = "opendata_";

  // Expression to search for
  private string $cmd_pattern;
  private DataLoader $dataLoader;
  private bool $isDataLoaderConnected;

  private function init(): void {
    $this->cmd_pattern           = '/{' . self::CMD . '(?P<cmd>\w+)\s(?P<product>\w+);(?P<size>\d*);(?P<text>.*?)(;(?P<rel>.*?))?}/i';
    $this->dataLoader            = new DataLoader();
    $this->isDataLoaderConnected = false;
  }

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

    $this->init();

    // find all matches of opendata image
    $text = $row->text;
    preg_match_all($this->cmd_pattern, $text, $matches);
    $countMatches = count($matches[0]);
    if ($countMatches) {
      try {
        $text = $this->process($text, $matches, $countMatches);
      }
      catch (Exception $e) {
        $this->error($e->getMessage());
      } finally {
        if ($this->isDataLoaderConnected) $this->dataLoader->disconnect();
      }
      $row->text = $text;
    }
  }

  private function process(string $text, array $matches, int $countMatches): string {
    for ($productIndex = 0; $productIndex < $countMatches; $productIndex++) {
      $product = $matches['product'][$productIndex];
      $cmd     = $matches['cmd'][$productIndex];

      // Fetch product
      try {
        if ($cmd == "get" || $cmd == "load") {
          $this->lazilyConnectDataLoader();
          $result = $this->dataLoader->loadProduct($product);
        }
        elseif ($cmd == "show") {
          $result = $this->dataLoader->loadFromCache($product);
        }
        else {
          continue;
        }
      }
      catch (Exception $e) {
        $this->error($e->getMessage());
        continue;
      }
      if ($result == null || $cmd == "load") continue;

      // Embed product
      $file        = $result[1];
      $productType = $result[0];
      $replace     = null;
      if ($this->dataLoader->isImage($productType)) {
        $file = Path::clean(substr($file, strlen(JPATH_BASE) + 1), '/');
        $file = Path::clean($file, '/');
        $size = $matches['size'][$productIndex];
        if (!$size) $size = "100";
        $img_text = $matches['text'][$productIndex];
        $img_rel  = $matches['rel'][$productIndex];
        if ($img_text) $replace = '<a href="' . $file . '" class="fancybox" rel="' . $img_rel . '" title="' . $img_text . '">'; // enable box
        $replace .= '<img width="' . $size . '%" src="' . $file . '"/>';
        if ($img_text) $replace .= '</a>';
      }
      else {
        $replace = file_get_contents($file);
      }
      if ($replace) {
        $text = str_replace($matches[0][$productIndex], $replace, $text);
      }
    }

    return $text;
  }

  /**
   * @throws Exception If connection fails.
   * @since 1.0.0
   */
  private function lazilyConnectDataLoader(): void {
    if (!$this->isDataLoaderConnected) {
      $this->dataLoader->connect();
      $this->isDataLoaderConnected = true;
    }
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
