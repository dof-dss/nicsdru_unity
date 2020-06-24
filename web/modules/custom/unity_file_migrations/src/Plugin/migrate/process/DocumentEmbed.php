<?php

namespace Drupal\unity_file_migrations\Plugin\migrate\process;

use Drupal\Core\Link;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\migrate\MigrateSkipRowException;
use GuzzleHttp\Exception\RequestException;

/**
 * Provides a 'DocumentEmbed' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "document_embed"
 * )
 */
class DocumentEmbed extends ProcessPluginBase {

  /**
   * @param $value
   *  Body Value.
   * @param MigrateExecutableInterface $migrate_executable
   *  Migrate Executable.
   * @param Row $row
   *  Row.
   * @param $destination_property
   *  Destination Property.
   * @return array|string|string[]|null
   *  Body Value return.
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    // Publication Page link used for replacing embedded links.
    $publications_link = Link::createFromRoute('Publication Page',
      'entity.node.canonical',
      ['node' => 2593],
      ['attributes' => ['rel' => 'nofollow', 'class' => 'publication_link']])->toString();

    // Extract and replace links.
    $embed_regex = '/<a[\w\s\.]*href="([\w:\-\/\.]*)(pdf|doc|doxc)[\w\s\.\=\-"\':><(&);\/]+<\/a>/U';
    $value = preg_replace($embed_regex, $publications_link, $value);

    return $value;
  }

}
