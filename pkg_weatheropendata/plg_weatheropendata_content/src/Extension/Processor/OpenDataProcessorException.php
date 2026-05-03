<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license         GNU GPL v3 or later
 */

namespace Weather\Plugin\Content\OpenData\Extension\Processor;

use Exception;
use Throwable;

defined('_JEXEC') or die('Restricted access');

/**
 * Indicates a recoverable logic error during open data command processing.
 *
 * @since       1.2.0
 */
class OpenDataProcessorException extends Exception
{

  /**
   * Constructor.
   */
  public function __construct(string $message, ?Throwable $previous = null)
  {
    parent::__construct($message, 0, $previous);
  }

}