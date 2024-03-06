<?php

namespace Weather\Plugin\Content\OpenData\Extension\Formatter;

defined('_JEXEC') or die('Restricted access');

/**
 * WMO-Produkt mit festen Zeilenumbrüchen, welche entfernt werden.
 *
 * @since 1.0.0
 */
class WMO_TXT implements Formatter {

  public function format(string $text): string {
    $wmoFormatter = new WMO();
    $text         = $wmoFormatter->format($text);
    $cleaned      = "";
    $paragraphs   = explode("\n\n", $text);
    foreach ($paragraphs as $paragraph) {
      $lines    = explode("\n", $paragraph);
      $cleaned  .= "<p>";
      $lastLine = count($lines) - 1;
      for ($i = 0; $i <= $lastLine; $i++) {
        $line              = trim($lines[$i]);
        $isLastLineInBlock = ($i == $lastLine);
        if (strlen($line) < 50 && !$isLastLineInBlock) {
          $line .= "<br/>";
        }
        else {
          $line .= " ";
        }
        $cleaned .= $line;
      }
      $cleaned .= "</p>";
    }

    return $cleaned;
  }

}