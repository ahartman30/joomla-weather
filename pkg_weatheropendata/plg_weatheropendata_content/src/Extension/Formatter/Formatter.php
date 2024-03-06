<?php

namespace Weather\Plugin\Content\OpenData\Extension\Formatter;
defined('_JEXEC') or die('Restricted access');

/**
 * Strategy responsible for formatting a downloaded text product.
 *
 * @since 1.0.0
 */
interface Formatter {

  /**
   * Processes the given text content and returns the formatted text.
   *
   * @param   string  $text  The text content to format.
   *
   * @return string The formatted content.
   *
   * @since 1.0.0
   */
  public function format(string $text): string;

}