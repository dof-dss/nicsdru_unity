<?php

namespace Drupal\unity_file_migrations\Commands;

use Drupal\unity_file_migrations\MigrationProcessors;
use Drush\Commands\DrushCommands;

/**
 * Drush custom commands.
 */
class UnityMigrationDrushCommands extends DrushCommands {

  /**
   * Drush command to publish Unity nodes after migration.
   *
   * @command post-migrate-publish
   */
  public function postMigratePublish() {
    $proc = new MigrationProcessors();
    $proc->updatePublishStatus();
  }

}
