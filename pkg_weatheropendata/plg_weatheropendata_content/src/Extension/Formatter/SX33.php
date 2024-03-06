<?php

namespace Weather\Plugin\Content\OpenData\Extension\Formatter;

defined('_JEXEC') or die('Restricted access');

/**
 * Synoptische Übersicht Mittelfrist
 *
 * @since 1.0.0
 */
class SX33 implements Formatter {

  public function format(string $text): string {
    $sections = preg_split("/\\r\\n_+\\r\\n\\r\\n/", mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1'));

    $head         = array_shift($sections);
    $head         = preg_split("/(\\r\\n\\r\\n)+/", $head);
    $summary      = $head[1];
    $head         = preg_split("/(\\r\\n)+/", $head[0]);
    $headLineDate = array_pop($head);
    $result       = "<h1>Synoptische &Uuml;bersicht Mittelfrist</h1>";
    $result       .= "<p>" . $headLineDate . "</p>";
    $result       .= "<p>" . $summary . "</p>";

    foreach ($sections as $section) {
      $paragraphs = preg_split("/\\s*\\r\\n(\\s*\\r\\n)+/", $section);
      if (count($paragraphs) == 1 && str_contains($paragraphs[0], "\r\n")) { # Abschnitt 'Basis für Mittelfristvorhersage'
        $paragraphs = explode("\r\n", $paragraphs[0]);
      }
      if (count($paragraphs) > 1) {
        $paragraph = array_shift($paragraphs);
        $result    .= "<h3>" . $paragraph . "</h3>";
      }
      foreach ($paragraphs as $paragraph) {
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
        $result  .= $cleaned;
      }
    }

    return $result;
  }

}
