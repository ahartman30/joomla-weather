<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license         GNU GPL v3 or later
 */

namespace Weather\Plugin\Content\OpenData\Extension\Processor\Strategies;

defined('_JEXEC') or die('Restricted access');

use Exception;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Path;
use Weather\Plugin\Content\OpenData\Extension\DataLoader;
use Weather\Plugin\Content\OpenData\Extension\Processor\OpenDataProcessorException;
use Weather\Plugin\Content\OpenData\Extension\Processor\OpenDataProcessorStrategy;

/**
 * Responsible for processing a product command.
 *
 * @since       1.2.0
 */
class ProductProcessor implements OpenDataProcessorStrategy {

  public const string CMD = "product";

  const string CMD_PRODUCT_LOAD = "load";
  const string CMD_PRODUCT_GET = "get";
  const string CMD_PRODUCT_SHOW = "show";

  private DataLoader $dataLoader;
  private bool $isDataLoaderConnected;


  /**
   * Constructor.
   */
  public function __construct()
  {
    $this->dataLoader            = new DataLoader();
    $this->isDataLoaderConnected = false;
  }

  public function execute(array $parameters, ?string $subCommand): string {
    if (count($parameters) < 1) throw new OpenDataProcessorException('Missing product name.');
    if (count($parameters) > 4) throw new OpenDataProcessorException('Too much product parameters.');
    $product = $parameters[0];
    $size = null;
    $imageText = null;
    $imageRel = null;
    if (count($parameters) >= 2) $size = $parameters[1];
    if (count($parameters) >= 3) $imageText = $parameters[2];
    if (count($parameters) == 4) $imageRel = $parameters[3];

    if ($subCommand === null) throw new OpenDataProcessorException('Missing product-command.');
    try {
      return $this->process($subCommand, $product, $size, $imageText, $imageRel);
    } catch (OpenDataProcessorException $e) {
      throw $e;
    } catch (Exception $e) {
      throw new OpenDataProcessorException(sprintf('Error processing "%s" for product "%s". %s', $subCommand, $product, $e->getMessage()), $e);
    }
  }

  private function process($command, $product, $size, $imageText, $imageRel): string {
    // Fetch product
    if ($command === self::CMD_PRODUCT_GET || $command === self::CMD_PRODUCT_LOAD) {
      $this->lazilyConnectDataLoader();
      $result = $this->dataLoader->loadProduct($product);
    }
    elseif ($command === self::CMD_PRODUCT_SHOW) {
      $result = $this->dataLoader->loadFromCache($product);
    }
    else {
      throw new OpenDataProcessorException("Unknown product-command: " . $command);
    }
    if ($result == null) throw new OpenDataProcessorException(sprintf('Product "%s" does not exist.', $product));
    if ($command == self::CMD_PRODUCT_LOAD) return '';

    // Create content
    $file = $result[1];
    $productType = $result[0];
    $content = null;
    if ($this->dataLoader->isImage($productType)) {
      $fileUrl = Path::clean(substr($file, strlen(JPATH_BASE) + 1), '/') . '?' . filemtime($file);
      if (!$size) $size = "100";
      if ($imageText) $content = '<a href="' . $fileUrl . '" class="fancybox" rel="' . $imageRel . '" title="' . $imageText . '">'; // enable box
      $content .= '<img width="' . $size . '%" src="' . $fileUrl . '"/>';
      if ($imageText) $content .= '</a>';
    } else {
      $content = file_get_contents($file);
    }
    return $content;
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

  public function finish(): void
  {
    if ($this->isDataLoaderConnected) $this->dataLoader->disconnect();
  }
}
