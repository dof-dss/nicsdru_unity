<?php

namespace Drupal\unity_file_migrations\Command;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\unity_file_migrations\MigrationProcessors;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
use Drupal\Core\Database\Database;
use Drupal\Console\Annotations\DrupalCommand;

/**
 * Class UnityMigratePostPublishStatusCommand.
 *
 * @DrupalCommand (
 *     extension="unity_file_migrations",
 *     extensionType="module"
 * )
 */
class UnityMigratePostPublishStatusCommand extends ContainerAwareCommand {

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
   * NodeMigrationProcessors definition.
   *
   * @var \Drupal\unity_file_migrations\MigrationProcessors
   */
  protected $migrationProcessors;

  /**
   * {@inheritdoc}
   */
  public function __construct(MigrationProcessors $migration_processors) {
    $this->dbConnMigrate = Database::getConnection('default', 'migrate');
    $this->dbConnDrupal8 = Database::getConnection('default', 'default');
    $this->migrationProcessors = $migration_processors;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  protected function configure() {
    $this->setName('unity:migrate:post:publish_status')
      ->setDescription($this->trans('commands.unity.migrate.post.publish_status.description'));
  }

  /**
   * {@inheritdoc}
   */
  protected function execute(InputInterface $input, OutputInterface $output) {

    $this->getIo()->info('Sync node publish status values after migration');

    // Find all out current node ids in the D8 site so we know what to look for.
    $d8_nids = [];
    $query = $this->dbConnDrupal8->query("SELECT nid FROM {node} ORDER BY nid ASC");
    $d8_nids = $query->fetchAllAssoc('nid');

    // Load source node publish status fields.
    $query = $this->dbConnMigrate->query("
      SELECT nid, status FROM {node}
      WHERE nid IN (:nids[])
      ORDER BY nid ASC
    ", [':nids[]' => array_keys($d8_nids)]);
    $migrate_nid_status = $query->fetchAll();

    // Sync our D8 node publish values and revisions with those from D7.
    foreach ($migrate_nid_status as $row) {
      $this->migrationProcessors->processNodeStatus($row->nid, $row->status);
    }

    $this->getIo()->info('Updated revisions on ' . count($migrate_nid_status) . ' nodes.');
    $this->getIo()->info('Clearing all caches...');
    drupal_flush_all_caches();
  }

}
