<?php

namespace Drupal\unity_file_migrations\Plugin\migrate\process;

use Drupal\migrate\MigrateException;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

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
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $matches = [];
    // Look for all anchors in the body field.
    if (preg_match_all('|href\=[\'"]+([^ >"\']*)[\'"]+[^>]*>|', $value['value'], $matches)) {
      if (count($matches) > 1) {
        foreach ($matches[1] as $original_link) {
          // So we have an anchor, does it look like a relative link
          // or an absolute?
          $matches2 = [];
          if (preg_match('|http://(.*)|', $original_link, $matches2) ||
            preg_match('|https://(.*)|', $original_link, $matches2)) {
            // This is an absolute URL does it match one
            // of the parameters to this plugin?
            $this->convertAbsoluteLink( $value, $original_link);
          }
        }
      }
    }
    return $value;
  }

  /**
   * Utility function to convert internal link to relative.
   */
  private function convertAbsoluteLink(&$value, $original_link) {
    // Get list of urls to replace.
    if (!isset($this->configuration['replace_urls'])) {
      throw new MigrateException('"replace_urls" must be configured.');
    }
    $replace_url_list = explode(" ", $this->configuration['replace_urls']);
    foreach ($replace_url_list as $replace_url) {
      // Does the start of this link match one of the
      // urls that we are looking for ?
      if (strpos($original_link, $replace_url) == 0) {
        // We have a match, change this absolute link
        // to a relative link.
        $value['value'] = str_replace($replace_url, '', $value['value']);
        $this->messenger()->addWarning("Replacing " . $original_link);
      }
    }
  }
}
