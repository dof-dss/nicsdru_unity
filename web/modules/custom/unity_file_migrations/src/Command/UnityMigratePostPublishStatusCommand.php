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
  public function __construct() {
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
    MigrationProcessors::updatePublishStatus($this->getIo());
  }

}
