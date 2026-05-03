<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license         GNU GPL v3 or later
 */

namespace Weather\Plugin\Content\OpenData\Extension\Processor\Strategies;

defined('_JEXEC') or die('Restricted access');

use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Filesystem\File;
use Weather\Plugin\Content\OpenData\Extension\OpenDataPlugin;
use Weather\Plugin\Content\OpenData\Extension\Processor\OpenDataProcessorException;
use Weather\Plugin\Content\OpenData\Extension\Processor\OpenDataProcessorStrategy;

/**
 * Responsible for processing an insert content command.
 *
 * @since       1.2.0
 */
class InsertContentProcessor implements OpenDataProcessorStrategy {

  public const string CMD = 'insert';

  private string $dataPath;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->dataPath = ComponentHelper::getParams('com_weatheropendata')->get('insertcontentDataPath') ?? '';
    $this->dataPath = OpenDataPlugin::resolveCleanAbsolutePath($this->dataPath);
  }

  public function execute(array $parameters, ?string $subCommand): string {
    if (count($parameters) < 1) throw new OpenDataProcessorException('Missing content file name.');
    if (count($parameters) > 1) throw new OpenDataProcessorException('Too much insert parameters.');
    $contentRelativePath = $parameters[0];

    try {
      return $this->process($contentRelativePath);
    } catch (OpenDataProcessorException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new OpenDataProcessorException(sprintf('Error processing insertfile of %s. %s', $contentRelativePath, $e->getMessage()), $e);
    }
  }

  private function process($contentRelativePath): string {
    $contentAbsolutePath = $this->dataPath . DIRECTORY_SEPARATOR . $contentRelativePath;
    $format = File::getExt($contentAbsolutePath);
    if (file_exists($contentAbsolutePath)) {
      $content = file_get_contents($contentAbsolutePath);
      if ($format == 'html') {
        $content = mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1');
      } else {
        $content = htmlspecialchars(mb_convert_encoding($content, 'UTF-8', 'ISO-8859-1'), ENT_COMPAT | ENT_HTML401, "UTF-8");
      }
      return $content;
    }
    else {
      throw new OpenDataProcessorException(sprintf('File to insert not exists: %s', $contentAbsolutePath));
    }
  }

  public function finish(): void {
  }

}