<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license         GNU GPL v3 or later
 */

namespace Weather\Plugin\Content\OpenData\Extension\Processor;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;
use Joomla\Registry\Registry;
use Weather\Plugin\Content\OpenData\Extension\DataLoader;
use Weather\Plugin\Content\OpenData\Extension\Processor\Strategies\ChartProcessor;
use Weather\Plugin\Content\OpenData\Extension\Processor\Strategies\InsertContentProcessor;
use Weather\Plugin\Content\OpenData\Extension\Processor\Strategies\ProductProcessor;

defined('_JEXEC') or die('Restricted access');

/**
 * Factory for getting strategy instances of OpenDataProcessor.
 *
 * @since 1.2.0
 */
class OpenDataProcessorFactory {

  private array $processors = [];
  private DatabaseInterface $db;
  private CMSApplicationInterface $app;
  private Registry $componentParams;

  /**
   * Constructor.
   *
   * @param DatabaseInterface $db
   * @param CMSApplicationInterface $app
   * @param Registry $componentParams The component parameters.
   */
  public function __construct(
    DatabaseInterface $db,
    CMSApplicationInterface $app,
    Registry $componentParams) {
    $this->db = $db;
    $this->app = $app;
    $this->componentParams = $componentParams;
  }

  /**
   * Returns the processor strategy instance for the given command.
   * Uses lazy instantiation.
   *
   * @param string $command The command to get the processor for.
   *
   * @return OpenDataProcessorStrategy instance for the given command.
   *
   * @throws openDataProcessorException if command is unknown.
   *
   * @since 1.2.0
   */
  public function getProcessor(string $command): OpenDataProcessorStrategy {
    if (isset($this->processors[$command])) {
      return $this->processors[$command];
    }

    if ($command === ProductProcessor::CMD) {
      $dataLoader = new DataLoader();
      $addImageUrlTimestamp = boolval($this->componentParams->get('productImageUrlTimestamp') ?? true);
      $openDataProcessor = new ProductProcessor($dataLoader, $addImageUrlTimestamp);
    } else if ($command === ChartProcessor::CMD) {
      $jsonDataDir = $this->componentParams->get('datapath') ?? '';
      $themeVersion = $this->componentParams->get('themeVersion') ?? '1';
      $mediaContentPath = 'media/com_weatheropendata/content';
      $addUrlTimestamp = boolval($this->componentParams->get('chartUrlTimestamp') ?? true);
      $openDataProcessor = new ChartProcessor(
        $this->db,
        $this->app,
        $jsonDataDir,
        $mediaContentPath,
        $themeVersion,
        $addUrlTimestamp);
    } else if ($command === InsertContentProcessor::CMD) {
      $dataPath = $this->componentParams->get('insertcontentDataPath') ?? '';
      $openDataProcessor = new InsertContentProcessor($dataPath);
    } else {
      throw new openDataProcessorException(sprintf('Command "%s" does not exist.', $command));
    }
    $this->processors[$command] = $openDataProcessor;
    return $openDataProcessor;
  }

  /**
   * Returns all used processors.
   *
   * @return array of OpenDataProcessorStrategy instances, with command as key
   * and instance as value.
   *
   * @since 1.2.0
   */
  public function getProcessors(): array {
    return $this->processors;
  }

}