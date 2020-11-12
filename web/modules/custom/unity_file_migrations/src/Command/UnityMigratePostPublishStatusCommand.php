<?php

namespace Drupal\unity_file_migrations\Command;

use Drupal\unity_file_migrations\MigrationProcessors;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Drupal\Console\Core\Command\ContainerAwareCommand;
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
