<?php

namespace Weather\Plugin\Content\OpenData\Extension\Formatter;

defined('_JEXEC') or die('Restricted access');

/**
 * HTML-Produkt als WMO-Bulletin
 *
 * @since 1.0.0
 */
class HTML implements Formatter {

  public function format(string $text): string {
    $wmoFormatter = new WMO();

    return $wmoFormatter->format($text);
  }

}