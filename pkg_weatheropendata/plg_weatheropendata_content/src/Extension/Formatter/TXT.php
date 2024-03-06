<?php

namespace Weather\Plugin\Content\OpenData\Extension\Formatter;

defined('_JEXEC') or die('Restricted access');

/**
 * Plain text product
 *
 * @since 1.0.0
 */
class TXT implements Formatter {

  public function format(string $text): string {
    return mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
  }

}