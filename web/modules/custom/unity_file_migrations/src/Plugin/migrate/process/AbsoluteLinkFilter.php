<?php

namespace Drupal\unity_file_migrations\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides an 'AbsoluteLinkFilter' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "absolute_link_filter"
 * )
 */
class AbsoluteLinkFilter extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property)
  {
    $matches = [];
    // Look for all anchors in the body field.
    if (preg_match_all('|href\=[\'"]+([^ >"\']*)[\'"]+[^>]*>|', $value['value'], $matches)) {
      if (count($matches) > 1) {
        foreach ($matches[1] as $original_link) {
          // So we have an anchor, does it look like a relative link or an absolute?
          $matches2 = [];
          if (preg_match('|http://(.*)|', $original_link, $matches2) ||
            preg_match('|https://(.*)|', $original_link, $matches2)) {
            // This is an absolute URL.
            // Does it look like a self link to Uregni ?
            if (count($matches2) > 1) {
              $remaining_url_portion = $matches2[1];
              if (preg_match('|[^\/]*uregni[^\/]*|', $remaining_url_portion)) {
                // This is a self link to Uregni that has been entered as an absolute path
                // so convert it into a relative path.
                // To do this, just take everything from the first '/'.
                $matches3 = [];
                if (preg_match('|[^ \/]*([\/])(.*)|', $remaining_url_portion, $matches3)) {
                  if (count($matches3) > 2) {
                    $relative_link = '/' . $matches3[2];
                    // Now replace all occurences of the original absolute link
                    // with the new relative link.
                    $value['value'] = str_replace($original_link, $relative_link, $value['value']);
                  }
                }
              }
            }
          }
        }
      }
    }
    return $value;
  }
}
