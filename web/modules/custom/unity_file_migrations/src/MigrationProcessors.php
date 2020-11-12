<?php

namespace Drupal\unity_file_migrations;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Driver\mysql\Connection;

/**
 * A collection of methods for processing migrations.
 *
 * @package Drupal\unity_file_migrations
 */
class MigrationProcessors {

  /**
   * Updates the status for nodes.
   */
  public static function updatePublishStatus($io, $node_type = NULL) {
    if (get_class($io) == 'Drupal\Console\Core\Style\DrupalStyle') {
      // Has been called from drupal console command line.
      $io->info('Sync node publish status values after migration');
    }
    else {
      // Has been called from post migration subscriber
      // class will be Drupal\Core\Logger\LoggerChannel.
      $io->notice('Sync node publish status values after migration');
    }

    $dbConnMigrate = Database::getConnection('default', 'migrate');
    $dbConnDrupal8 = Database::getConnection('default', 'default');

    // Find all node ids in the D8 site so we know what to look for.
    $d8_nids = [];
    if ($node_type) {
      $query = $dbConnDrupal8->query("SELECT nid FROM {node} WHERE type = :node_type ORDER BY nid ASC", [':node_type' => $node_type]);
      $d8_nids = $query->fetchAllAssoc('nid');
    }
    else {
      $query = $dbConnDrupal8->query("SELECT nid FROM {node} ORDER BY nid ASC");
      $d8_nids = $query->fetchAllAssoc('nid');
    }

    // Load source node publish status fields.
    $query = $dbConnMigrate->query("
      SELECT nid, status FROM {node}
      WHERE nid IN (:nids[])
      ORDER BY nid ASC
    ", [':nids[]' => array_keys($d8_nids)]);
    $migrate_nid_status = $query->fetchAll();

    // Sync our D8 node publish values and revisions with those from D7.
    foreach ($migrate_nid_status as $row) {
      self::processNodeStatus($dbConnMigrate, $dbConnDrupal8, $row->nid, $row->status);
    }

    if (get_class($io) == 'Drupal\Console\Core\Style\DrupalStyle') {
      $io->info('Updated revisions on ' . count($migrate_nid_status) . ' nodes.');
      $io->info('Clearing all caches...');
    }
    else {
      $io->notice('Updated revisions on ' . count($migrate_nid_status) . ' nodes.');
      $io->notice('Clearing all caches...');
    }
    drupal_flush_all_caches();
  }

  /**
   * Updates the status and revisions for the specified node.
   *
   * @param Drupal\Core\Database\Driver\mysql\Connection $dbConnMigrate
   *   Migration source db.
   * @param Drupal\Core\Database\Driver\mysql\Connection $dbConnDrupal8
   *   Migration destination db.
   * @param int $nid
   *   The node id.
   * @param string $status
   *   The status of the node.
   */
  public static function processNodeStatus(Connection $dbConnMigrate, Connection $dbConnDrupal8, int $nid, string $status) {
    // Need to fetch the D8 revision ID for any node as it doesn't
    // always match the source db.
    $d8_vid = $dbConnDrupal8->query(
      "SELECT vid FROM {node_field_data} WHERE nid = :nid", [':nid' => $nid]
    )->fetchField();

    // Run an update statement per item. Refinement might be to run a
    // cross-DB SELECT query to power an UPDATE using a JOIN.
    $query = $dbConnDrupal8->update('node_field_data')
      ->fields(['status' => $status])
      ->condition('nid', $nid)
      ->execute();

    $query = $dbConnDrupal8->update('node_field_revision')
      ->fields(['status' => $status])
      ->condition('nid', $nid)
      ->condition('vid', $d8_vid)
      ->execute();

    // Get the D7 revision id.
    $vid = $dbConnMigrate->query(
      "SELECT vid FROM {node} WHERE nid = :nid", [':nid' => $nid]
    )->fetchField();

    // Update the current revision if necessary.
    if ($vid != $d8_vid) {
      $vid = self::updateCurrentRevision($dbConnMigrate, $dbConnDrupal8, $nid, $vid, $d8_vid);
    }

    // The 'revision_translation_affected' field is poorly documented (and
    // understood) in Drupal core, and is sometimes set to NULL after migrating
    // content from Drupal 7. There is much discussion at
    // https://www.drupal.org/project/drupal/issues/2746541 but after testing
    // and investigation I am yet to find a case where it should not be
    // set to '1'.
    // Hence, we set it to '1' across the board to solve the problem
    // of revisions not appearing on the revisions tab.
    $query = $dbConnDrupal8->update('node_field_revision')
      ->fields(['revision_translation_affected' => 1])
      ->condition('nid', $nid)
      ->execute();

    // Make sure that we have a 'published' revision.
    $query = $dbConnDrupal8->update('content_moderation_state_field_data')
      ->fields(['moderation_state' => 'published'])
      ->condition('content_entity_id', $nid)
      ->condition('content_entity_revision_id', $vid)
      ->execute();

    $query = $dbConnDrupal8->update('content_moderation_state_field_revision')
      ->fields(['moderation_state' => 'published'])
      ->condition('content_entity_id', $nid)
      ->condition('content_entity_revision_id', $vid)
      ->execute();

    if ($status == 1) {
      // Only one row in node_field_revision should be set to
      // 'published' for this nid.
      $query = $dbConnDrupal8->update('node_field_revision')
        ->fields(['status' => 0])
        ->condition('nid', $nid)
        ->condition('vid', $vid, '<>')
        ->execute();

      $query = $dbConnDrupal8->update('node_field_revision')
        ->fields(['status' => 1])
        ->condition('nid', $nid)
        ->condition('vid', $vid)
        ->execute();
    }
  }

  /**
   * Updates the current revision for the given node.
   *
   * @param Drupal\Core\Database\Driver\mysql\Connection $dbConnMigrate
   *   Migration source db.
   * @param Drupal\Core\Database\Driver\mysql\Connection $dbConnDrupal8
   *   Migration destination db.
   * @param int $nid
   *   The node id.
   * @param int $vid
   *   The target revision id (from D7).
   * @param int $d8_vid
   *   The current D8 revision id.
   *
   * @return string
   *   Current revision id.
   */
  public static function updateCurrentRevision(Connection $dbConnMigrate, Connection $dbConnDrupal8, int $nid, int $vid, int $d8_vid) {
    // Does this revision exist in D8 ?
    $check_vid = $dbConnDrupal8->query(
      "SELECT vid FROM {node_field_revision} WHERE nid = :nid AND vid = :vid", [':nid' => $nid, ':vid' => $vid]
    )->fetchField();
    if (!empty($check_vid)) {
      // Does the current D8 revision exist in D7 ?
      $check_d7_vid = $dbConnMigrate->query(
        "SELECT vid FROM {node_revision} WHERE nid = :nid and vid = :vid", [':nid' => $nid, ':vid' => $d8_vid]
      )->fetchField();
      if (!empty($check_d7_vid)) {
        // Make the D7 revision the current revision in D8.
        // N.B. This will only work in the 'one hit' migration scenario, it may
        // cause problems if the migration runs again and in the meantime the
        // editors have reverted to an older revision that also came from D7.
        $query = $dbConnDrupal8->update('node')
          ->fields(['vid' => $vid])
          ->condition('nid', $nid)
          ->execute();
        $query = $dbConnDrupal8->update('node_field_data')
          ->fields(['vid' => $vid])
          ->condition('nid', $nid)
          ->execute();
      }
      return $vid;
    }
    else {
      return $d8_vid;
    }
  }

}
