<?php

namespace Weather\Plugin\Content\OpenData\Extension\Formatter;

defined('_JEXEC') or die('Restricted access');

/**
 * Synoptische Übersicht Kurzfrist
 *
 * @since 1.0.0
 */
class SX31 implements Formatter {

  public function format(string $text): string {
    $paragraphs = preg_split("/\\s*\\r\\n(\\s*\\r\\n)+/", mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1'));

    $head         = array_shift($paragraphs);
    $headLineDate = explode("\r\n", $head);
    $headLineDate = array_pop($headLineDate);
    $result       = "<h1>Synoptische &Uuml;bersicht Kurzfrist</h1>";
    $result       .= "<p>" . $headLineDate . "</p>";

    foreach ($paragraphs as $paragraph) {
      /* Process headline */
      if (str_contains($paragraph, "------------------------------")) {
        $paragraph = explode("\r\n", $paragraph);
        $result    .= "<h3>" . str_replace("\r\n", "", array_shift($paragraph)) . "</h3>";
        array_shift($paragraph);
        $paragraph = implode("\r\n", $paragraph);
      }

      $lines    = explode("\r\n", $paragraph);
      $cleaned  = "<p>";
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

      /* Process GWL */
      if (str_contains($cleaned, "GWL ")) {
        $dictionary = $this->getGwlDictionary();
        foreach ($dictionary as $gwlSymbol => $text) {
          $cleaned = preg_replace('/\b' . $gwlSymbol . '\b/', '<abbr title="' . $text . '">' . $gwlSymbol . '</abbr>', $cleaned);
        }
      }

      $result .= $cleaned;
    }

    return $result;
  }

  private function getGwlDictionary(): array {
    $lines      = file(JPATH_PLUGINS . "/content/weatheropendata/src/Extension/formatter/GWL.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $dictionary = array();
    foreach ($lines as $line) {
      $entry = explode("|", $line);
      $keys  = explode(",", $entry[0]);
      foreach ($keys as $key) {
        $dictionary[$key] = $entry[1];
      }
    }

    return $dictionary;
  }

}
