<?php

namespace Weather\Plugin\Content\OpenData\Extension\Formatter;

defined('_JEXEC') or die('Restricted access');

/**
 * Thema des Tages - Überschrift
 *
 * @since 1.0.0
 */
class FP13_TITLE implements Formatter {

  public function format(string $text): string {
    $headLine   = "";
    $paragraphs = explode("\r\n\r\n", mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1'));
    do {
      $headLine = array_shift($paragraphs);
      $headLine = str_ireplace("Wetter aktuell", "", $headLine);
      $headLine = str_ireplace("Wissenschaft Kompakt", "", $headLine);
      $headLine = trim($headLine);
    } while (strlen($headLine) == 0);

    return $headLine;
  }

}