<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license         GNU GPL v3 or later
 */

namespace Weather\Plugin\Content\OpenData\Extension;

defined('_JEXEC') or die('Restricted access');

use Exception;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Event\Content\ContentPrepareEvent;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseAwareTrait;
use Joomla\Event\SubscriberInterface;
use Joomla\Filesystem\Path;
use Joomla\String\StringHelper;
use RuntimeException;
use Weather\Plugin\Content\OpenData\Extension\Processor\OpenDataProcessorException;
use Weather\Plugin\Content\OpenData\Extension\Processor\OpenDataProcessorFactory;
use Weather\Plugin\Content\OpenData\Extension\Processor\OpenDataProcessorStrategy;

/**
 * Plugin for embedding opendata content.
 *
 * @package     Weather\Plugin\Content\OpenData\Extension
 *
 * @since       1.0.0
 */
class OpenDataPlugin extends CMSPlugin implements SubscriberInterface {

  use DatabaseAwareTrait;

  public const string CMD = "opendata:";
  private const string CMD_PATTERN  = '/{' . self::CMD . '(?P<command>\w+?)(-(?P<subcommand>\w+?))?\s(?P<parameters>.+?)}/';
  private OpenDataProcessorFactory $processorFactory;

  /**
   * Returns an array of events this subscriber will listen to.
   *
   * @return array
   *
   * @since   1.2.0
   */
  public static function getSubscribedEvents(): array
  {
    return [
      'onContentPrepare' => 'onContentPrepare',
    ];
  }

  /**
   * Resolves and cleans an absolute path relative to the Joomla base path.
   *
   * @param string $relativePath The path to resolve.
   *
   * @return string The resolved and cleaned path.
   *
   * @since 1.2.0
   */
  public static function resolveCleanAbsolutePath(string $relativePath): string {
    $path = JPATH_BASE . DIRECTORY_SEPARATOR . $relativePath;
    $path = Path::clean($path);
    return realpath($path);
  }

  /**
   * Plugin that adds opendata content to the article.
   *
   * This is the first stage in preparing content for output and is the most common point for content orientated
   * plugins to do their work. Since the article and related parameters are passed by reference, event handlers can
   * modify them prior to display.
   *
   * @param   ContentPrepareEvent $event  The event instance.
   *
   * @return  void
   */
  public function onContentPrepare(ContentPrepareEvent $event)
  {
    $context = $event->getContext();
    $row     = $event->getItem();

    $canProceed = ($context === 'com_content.article' || $context === 'mod_custom.content')
      && $this->getApplication()->isClient('site') // Skip processing in admin interface.
      && StringHelper::strpos($row->text, self::CMD) !== false; // Simple performance check.
    if (!$canProceed) return;

    // find all matches
    $text = $row->text;
    $countMatches = preg_match_all(self::CMD_PATTERN, $text, $matches);
    if ($countMatches === false) {
      $this->error(sprintf('Error on regex %s: %s', htmlspecialchars(self::CMD_PATTERN), preg_last_error_msg()));
      return;
    }
    $this->processorFactory = new OpenDataProcessorFactory($this->getDatabase(), $this->getApplication());
    try {
      $text = $this->process($text, $matches, $countMatches);
    } catch (Exception $e) {
      $this->error($e->getMessage());
    }
    $row->text = $text;
  }

  private function process(string $text, array $matches, int $countMatches): string {
    for ($cmdIndex = 0; $cmdIndex < $countMatches; $cmdIndex++) {
      $fullMatch = $matches[0][$cmdIndex];
      $command = $matches['command'][$cmdIndex];
      $subCommand = $matches['subcommand'][$cmdIndex];
      $parameters = explode(';', $matches['parameters'][$cmdIndex]);

      /** @var OpenDataProcessorStrategy $openDataProcessor */
      try
      {
        $openDataProcessor = $this->processorFactory->getProcessor($command);
        $content = $openDataProcessor->execute($parameters, $subCommand);
        $text = str_replace($fullMatch, $content, $text);
      } catch (OpenDataProcessorException $e) {
        $this->error(sprintf('Error processing command "%s": %s', $command, $e->getMessage()));
        continue;
      }
    }
    $this->finish();
    return $text;
  }

  private function finish(): void
  {
    foreach ($this->processorFactory->getProcessors() as $openDataProcessor) {
      $openDataProcessor->finish();
    }
  }

  private function error(string $msg): void {
    if ($this->loggedIn()) {
      $this->getApplication()->enqueueMessage($msg, CMSApplicationInterface::MSG_ERROR);
    }
  }

  private function loggedIn(): bool {
    try {
      $user = $this->getApplication()->getIdentity();
    } catch (Exception $e) {
      throw new RuntimeException("Getting Application object failed.", $e);
    }
    if ($user->id) {
      return true;
    }
    return false;
  }

}
