<?php
/**
 * @copyright   (C) 2026 Alexander Hartmann
 * @license         GNU GPL v3 or later
 */

namespace Weather\Plugin\Content\OpenData\Extension\Processor;

defined('_JEXEC') or die('Restricted access');

/**
 * Strategy responsible for processing an opendata command.
 *
 * @since 1.2.0
 */
interface OpenDataProcessorStrategy {

  /**
   * Processes the given command and returns the content as a replacement in the article text.
   *
   * @param   array        $parameters  The parameters for the command.
   * @param   string|null  $subCommand  The sub command to execute, if supported.
   *
   * @return string The created content.
   * @throws OpenDataProcessorException On error processing the command.
   *
   * @since 1.2.0
   */
  public function execute(array $parameters, ?string $subCommand): string;

  /**
   * Performs any necessary cleanup after processing.
   *
   * @since 1.2.0
   */
  public function finish(): void;

}