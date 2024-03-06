<?php

namespace Weather\Plugin\Content\InsertText\Extension;

use Exception;
use http\Exception\RuntimeException;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Filesystem\Path;
use Joomla\String\StringHelper;

defined('_JEXEC') or die('Restricted access');

/**
 * Plugin for inserting text and html into an article content.
 *
 * @package     Weather\Plugin\Content\InsertText\Extension
 *
 * @since       1.0.0
 */
class InsertText extends CMSPlugin {

  const CMD = "insert_text";

  private string $pattern;
  private string $dir;


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
    $this->pattern = "/{" . self::CMD . " (?P<filename>.+?)\.(?P<extension>.+)}/i";

    // simple performance check to determine if to proceed
    if (!$this->getApplication()->isClient('site')) return; // Skip processing in admin interface
    if (StringHelper::strpos($row->text, self::CMD) === false) return;

    $this->dir = $this->resolveCleanAbsolutePath($this->params->get('dir'));

    // Find all matches of placeholders.
    $text = $row->text;
    preg_match_all($this->pattern, $text, $matches);
    $countMatches = count($matches[0]);
    if ($countMatches) {
      $text      = $this->process($text, $matches, $countMatches);
      $row->text = $text;
    }
  }

  private function resolveCleanAbsolutePath(string $relativePath): string {
    $path = JPATH_BASE . '/' . $relativePath . '/';
    $path = Path::clean($path);

    return realpath($path);
  }

  private function process(string $text, array $matches, int $countMatches): string {
    for ($i = 0; $i < $countMatches; $i++) {
      $filename = $this->dir . '/' . $matches['filename'][$i];
      $format   = $matches['extension'][$i];
      $file     = $filename . "." . $format;
      if (file_exists($file)) {
        $content = file_get_contents($file);
        if ($format == 'html') {
          $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
        }
        else {
          $content = htmlspecialchars(mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1'), ENT_COMPAT | ENT_HTML401, "UTF-8");
        }
        $text = str_replace($matches[0][$i], $content, $text);
      }
      else {
        $this->error(basename($file) . ": Text existiert nicht.");
      }
    }

    return $text;
  }

  private function error($msg): void {
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