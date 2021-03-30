<?php

namespace Drupal\unity_file_migrations;

use Drupal\Core\Database\Database;
use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A collection of methods for processing migrations.
 *
 * @package Drupal\unity_file_migrations
 */
class MigrationProcessors {

  /**
   * Drupal\Core\Extension\ModuleHandler definition.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * Node Storage definition.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $nodeStorage;

  /**
   * Migration database connection (Drupal 7).
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnMigrate;

  /**
   * Drupal 8 database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $dbConnDrupal8;

  /**
   * {@inheritdoc}
   */
  public function __construct(ModuleHandler $module_handler, EntityTypeManagerInterface $entity_type_manager) {
    $this->moduleHandler = $module_handler;
    $this->nodeStorage = $entity_type_manager->getStorage('node');
    $this->dbConnMigrate = Database::getConnection('default', 'migrate');
    $this->dbConnDrupal8 = Database::getConnection('default', 'default');
  }

  /**
   * Updates the status for nodes.
   */
  public function updatePublishStatus($io, $node_type = NULL) {
    if (get_class($io) == 'Drupal\Console\Core\Style\DrupalStyle') {
      // Has been called from drupal console command line.
      $io->info('Sync node publish status values after migration');
    }
    else {
      // Has been called from post migration subscriber
      // class will be Drupal\Core\Logger\LoggerChannel.
      $io->notice('Sync node publish status values after migration');
    }

    // Find all node ids in the D8 site so we know what to look for.
    $d8_nids = [];
    if ($node_type) {
      $query = $this->dbConnDrupal8->query("SELECT nid FROM {node} WHERE type = :node_type ORDER BY nid ASC", [':node_type' => $node_type]);
      $d8_nids = $query->fetchAllAssoc('nid');
    }
    else {
      $query = $this->dbConnDrupal8->query("SELECT nid FROM {node} ORDER BY nid ASC");
      $d8_nids = $query->fetchAllAssoc('nid');
    }

    // Load source node publish status fields.
    $query = $this->dbConnMigrate->query("
      SELECT nid, status FROM {node}
      WHERE nid IN (:nids[])
      ORDER BY nid ASC
    ", [':nids[]' => array_keys($d8_nids)]);
    $migrate_nid_status = $query->fetchAll();

    // Sync our D8 node publish values and revisions with those from D7.
    //$this->processNodeStatus(3531, 1);
    foreach ($migrate_nid_status as $row) {
      $this->processNodeStatus($row->nid, $row->status, $io);
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
   * @param int $nid
   *   The node id.
   * @param string $status
   *   The status of the node.
   */
  public function processNodeStatus(int $nid, string $status, $io) {
    // Need to fetch the D8 revision ID for any node as it doesn't
    // always match the source db.
    $d8_vid = $this->dbConnDrupal8->query(
      "SELECT vid FROM {node_field_data} WHERE nid = :nid", [':nid' => $nid]
    )->fetchField();

    // Get the D7 revision id.
    $vid = $this->dbConnMigrate->query(
      "SELECT vid FROM {node} WHERE nid = :nid", [':nid' => $nid]
    )->fetchField();


    // Update the current revision if necessary.
    if ($vid != $d8_vid) {
      // Does this revision exist in D8 ?
      $check_vid = $this->dbConnDrupal8->query(
        "SELECT vid FROM {node_field_revision} WHERE nid = :nid AND vid = :vid",
        [':nid' => $nid, ':vid' => $vid]
      )->fetchField();
      if (!empty($check_vid)) {
        $io->info('Updating node ' . $nid . ' with old revision ' . $vid);
        $revision = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($vid);
        $revision->isDefaultRevision(TRUE);
        if ($status == 1) {
          $revision->setpublished();
        }
        $revision->save();
      }
    }

    if ($status == 1) {
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
      $node->status = 1;
      $node->set('moderation_state', 'published');
      $node->save();
    }
    else {
      // See if the moderation state on D7 was 'needs review'.
      $moderation_status = $this->dbConnMigrate->query("
        select state from {workbench_moderation_node_history}
        where hid = (select max(hid) from {workbench_moderation_node_history} where nid = :nid)
          ", [':nid' => $nid])->fetchField();
      if ($moderation_status == 'needs_review') {
        $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);
        $node->set('moderation_state', 'needs_review');
        $node->save();
      }
    }
  }

  /**
   * Updates the current revision for the given node.
   *
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
  public function updateCurrentRevision(int $nid, int $vid, int $d8_vid) {
    // Does this revision exist in D8 ?
    $check_vid = $this->dbConnDrupal8->query(
      "SELECT vid FROM {node_field_revision} WHERE nid = :nid AND vid = :vid",
      [':nid' => $nid, ':vid' => $vid]
    )->fetchField();
    if (!empty($check_vid)) {
      // Does the current D8 revision exist in D7 ?
      $check_d7_vid = $this->dbConnMigrate->query(
        "SELECT vid FROM {node_revision} WHERE nid = :nid and vid = :vid",
        [':nid' => $nid, ':vid' => $d8_vid]
      )->fetchField();
      if (!empty($check_d7_vid)) {
        // Make the D7 revision the current revision in D8.
        // N.B. This will only work in the 'one hit' migration scenario, it may
        // cause problems if the migration runs again and in the meantime the
        // editors have reverted to an older revision that also came from D7.
        $revision = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($vid);
        $revision->isDefaultRevision(TRUE);
        $revision->setpublished();
        $revision->save();
        /*$query = $this->dbConnDrupal8->update('node')
          ->fields(['vid' => $vid])
          ->condition('nid', $nid)
          ->execute();
        $query = $this->dbConnDrupal8->update('node_field_data')
          ->fields(['vid' => $vid])
          ->condition('nid', $nid)
          ->execute();*/
      }
      return $vid;
    }
    else {
      return $d8_vid;
    }
  }

}
