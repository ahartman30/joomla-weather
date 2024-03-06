<?php

namespace Weather\Plugin\Content\OpenData\Extension\Formatter;

defined('_JEXEC') or die('Restricted access');

/**
 * WMO-Bulletin
 *
 * @since 1.0.0
 */
class WMO implements Formatter {

  public function format(string $text): string {
    $cleaned  = "";
    $lines    = explode("\r\r\n", $text);
    $lastLine = count($lines) - 1;
    for ($i = 0; $i <= $lastLine; $i++) {
      $line = trim($lines[$i]);
      if ($i < 2 || $i == $lastLine) continue; // remove WMO header and footer
      $cleaned .= $line;
      $cleaned .= "\n";
    }
    $cleaned = mb_convert_encoding($cleaned, 'UTF-8', 'ISO-8859-1');

    return $cleaned;
  }

}