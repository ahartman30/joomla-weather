<?php

namespace Weather\Plugin\Content\OpenData\Extension\Formatter;

defined('_JEXEC') or die('Restricted access');

/**
 * Vorhersage
 *
 * @since 1.0.0
 */
class VHDL implements Formatter {

  public function format(string $text): string {
    $result = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1');
    $result = str_replace('<br /><br />', '', $result);
    $result = preg_replace('/<pre.*?>(.+?)<\/pre>/s', '<p style="white-space:pre-wrap;">$1</p>', $result);
    $result = preg_replace('/^<p>(.+)<\/p>/s', '$1', $result);

    return "<div>" . $result . "</div>";
  }

}
