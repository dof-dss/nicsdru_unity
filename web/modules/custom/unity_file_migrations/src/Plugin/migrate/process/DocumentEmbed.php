<?php

namespace Drupal\unity_file_migrations\Plugin\migrate\process;

use Drupal\Core\Link;
use Drupal\Core\Menu\MenuLinkTree;
use Drupal\Core\Menu\MenuTreeParameters;
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
class DocumentEmbed extends ProcessPluginBase
{

    /**
     * @param  $value
     *  Body Value.
     * @param  MigrateExecutableInterface $migrate_executable
     *  Migrate Executable.
     * @param  $row
     *  Row.
     * @param  $destination_property
     *  Destination Property.
     * @return array|string|string[]|null
     *  Body Value return.
     */
    public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property)
    {

        // Create REGEX string to match file links.
        $embed_regex = '/<a[\w\s\.]*href="([\w:\-\/\.]*)(pdf|doc|docx)[\w\s\.\=\-"\':><(&);\/]+<\/a>/U';

        // Search for matches in body value.
        $matches = [];
        preg_match_all($embed_regex, $value['value'], $matches, PREG_SET_ORDER);

        // Iterate these matches to find publications ( if exists ).
        if (!empty($matches)) {

            foreach($matches as $match) {

                // Validation to check the preg match returned array result.
                if (empty($match[0]) || empty($match[1])) {
                    continue;
                }

                $query = \Drupal::entityQuery('node')
                    ->condition('type', 'publication_page')
                    ->condition('body', basename($match[1], "."), 'CONTAINS');

                $nids = $query->execute();

                // Node has returned with file included. Creating link to specific node.
                if ($lastNode = end($nids)) {
                    // Publication Page link used for replacing embedded links.
                    $publications_link = Link::createFromRoute(
                        'Specific Publication Page',
                        'entity.node.canonical',
                        ['node' => $lastNode],
                        ['attributes' => ['rel' => 'nofollow', 'class' => 'publication_link']]
                    )->toString();
                } else {
                    // Else, just create route to default publication page
                    // Publication Page link used for replacing embedded links.
                    // Currently hardcoded node ID but will be dynamic to prevent ID issues.
                    $publications_link = Link::createFromRoute(
                        'Default Publication Page',
                        'entity.node.canonical',
                        ['node' => 2593],
                        ['attributes' => ['rel' => 'nofollow', 'class' => 'publication_link']]
                    )->toString();
                }

                // Then replace these links to publication link ( or publication page if not exists -- for now ).
                //            $value = preg_replace($embed_regex, $publications_link, $value);

                $value = str_replace($match[0], $publications_link, $value); // Str replace for multiple matches within body value
            }
        }

        return $value;
    }

}
