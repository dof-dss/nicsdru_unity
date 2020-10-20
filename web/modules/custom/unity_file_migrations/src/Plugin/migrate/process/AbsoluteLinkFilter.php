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
            // This is an absolute URL.
            // Does it look like a self link to Uregni ?
            $this->convertUregniLink($matches2, $value, $original_link);
          }
        }
      }
    }
    return $value;
  }

  /**
   * Utility function to convert internal Uregni link to relative.
   */
  private function convertUregniLink($matches, &$value, $original_link) {
    if (count($matches) > 1) {
      $remaining_url_portion = $matches[1];
      // Get site name to search for and replace.
      if (!isset($this->configuration['site_name'])) {
        throw new MigrateException('"site_name" must be configured.');
      }
      // Does the domain name contain the site name ?
      $regex = '|[^\/]*' . $this->configuration['site_name'] . '[^\/]*|';
      if (preg_match($regex, $remaining_url_portion)) {
        // This is a self link to <site_name> that has been entered as an
        // absolute path so convert it into a relative path.
        // To do this, just take everything from the first '/'.
        $matches2 = [];
        if (preg_match('|[^ \/]*([\/])(.*)|', $remaining_url_portion, $matches2)) {
          if (count($matches2) > 2) {
            $relative_link = '/' . $matches2[2];
            // Get specified patterns to search for and replace.
            $replace = 'files/' . $this->configuration['site_name'];
            if (isset($this->configuration['test_url_1'])) {
              // Catch this alternative format too.
              $regex = 'sites/' . $this->configuration['test_url_1'] . '/files';
              $relative_link = str_replace($regex, $replace, $relative_link);
            }
            // Now we just need to convert /sites/<site_name>/files location
            // to /files/<site_name> for D8.
            $regex = 'sites/' . $this->configuration['site_name'] . '/files';
            $relative_link = str_replace($regex, $replace, $relative_link);
            // Now replace all occurences of the original absolute link
            // with the new relative link.
            $value['value'] = str_replace($original_link, $relative_link, $value['value']);
          }
        }
      }
    }
  }

}
