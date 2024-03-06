<?php

namespace Weather\Plugin\Content\OpenData\Extension\Formatter;

defined('_JEXEC') or die('Restricted access');

/**
 * Vorhersage Schlagzeile
 *
 * @since 1.0.0
 */
class VHDL50_TITLE implements Formatter {

  public function format(string $text): string {
    $text = preg_replace('/\r*\n*/', '', mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1'));
    preg_match('/<strong>(.+?)<\/strong>/', $text, $matches);
    $result = trim($matches[1]);

    return $result;
  }

}
