<?php

namespace Weather\Plugin\Content\OpenData\Extension\Formatter;

defined('_JEXEC') or die('Restricted access');

/**
 * Thema des Tages
 *
 * @since 1.0.0
 */
class FP13 implements Formatter {

  public function format(string $text): string {
    $cleaned    = "";
    $headLine   = "";
    $paragraphs = explode("\r\n\r\n", mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1'));
    do {
      $headLine = array_shift($paragraphs);
      $headLine = str_ireplace("Wetter aktuell", "", $headLine);
      $headLine = str_ireplace("Wissenschaft Kompakt", "", $headLine);
      $headLine = trim($headLine);
    } while (strlen($headLine) == 0);
    $cleaned .= "<h3>" . trim($headLine) . "</h3>";
    foreach ($paragraphs as $paragraph) {
      $lines    = explode("\r\n", $paragraph);
      $cleaned  .= "<p>";
      $lastLine = count($lines) - 1;
      for ($i = 0; $i <= $lastLine; $i++) {
        $line              = trim($lines[$i]);
        $isLastLineInBlock = ($i == $lastLine);
        $isNumberLine      = false;
        if (!$isLastLineInBlock) {
          $nextLine     = trim($lines[$i + 1]);
          $isNumberLine = (substr($nextLine, 1, 1) == "."); // 1. oder 2. oder 3. etc.
        }
        if ((strlen($line) < 50 || $isNumberLine) && !$isLastLineInBlock) {
          $line .= "<br/>";
        }
        else {
          $line .= " ";
        }
        $cleaned .= $line;
      }
      $cleaned .= "</p>";
    }
    $cleaned = $this->makeClickableLinks($cleaned);
    $cleaned = $this->makeClickableDwdLinks($cleaned);

    return $cleaned;
  }

  private function makeClickableLinks(string $s): string {
    return preg_replace('@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', '<a href="$1" target="_blank">$1</a>', $s);
  }

  private function makeClickableDwdLinks(string $s): string {
    return preg_replace('@(\s)(www.dwd.de/([-\w\.]+[-\w])+(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@', ' <a href="http://$2" target="_blank">$2</a>', $s);
  }

}