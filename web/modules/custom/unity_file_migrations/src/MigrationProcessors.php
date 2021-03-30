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
    foreach ($migrate_nid_status as $row) {
      $this->processNodeStatus($row->nid, $row->status);
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
  public function processNodeStatus(int $nid, string $status) {
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
        // Revision exists, make it current (and publish if necessary)
        $revision = $this->nodeStorage->loadRevision($vid);
        $revision->isDefaultRevision(TRUE);
        if ($status == 1) {
          $revision->setpublished();
        }
        $revision->save();
      }
    }

    if ($status == 1) {
      // If node was published on D7, make sure that it is published on D8.
      $node = $this->nodeStorage->load($nid);
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
        // Make sure state is 'needs review' on D8.
        $node = $this->nodeStorage->load($nid);
        $node->set('moderation_state', 'needs_review');
        $node->save();
      }
    }
  }

}
