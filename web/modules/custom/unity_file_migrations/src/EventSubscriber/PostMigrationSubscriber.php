<?php

namespace Drupal\unity_file_migrations\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\unity_file_migrations\MigrationProcessors;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class PostMigrationSubscriber.
 *
 * Post Migrate processes.
 */
class PostMigrationSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Logger\LoggerChannelFactory definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactory
   */
  protected $logger;

  /**
   * NodeMigrationProcessors definition.
   *
   * @var \Drupal\migrate_nidirect_utils\MigrationProcessors
   */
  protected $migrationProcessors;

  /**
   * Stores the entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Stores the migration prefix.
   *
   * @var string
   */
  protected $migrationPrefix;

  /**
   * PostMigrationSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The Entity manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   Drupal logger.
   * @param \Drupal\migrate_nidirect_utils\MigrationProcessors $migration_processors
   *   Migration processors.
   * @param string $migration_prefix
   *   To identify node migrations.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager,
                              LoggerChannelFactory $logger,
                              MigrationProcessors $migration_processors,
                              string $migration_prefix) {
    $this->logger = $logger->get('unity_file_migrations');
    $this->migrationProcessors = $migration_processors;
    $this->entityTypeManager = $entity_type_manager;
    $this->migrationPrefix = $migration_prefix;
  }

  /**
   * Get subscribed events.
   *
   * @inheritdoc
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_IMPORT][] = ['onMigratePostImport'];
    return $events;
  }

  /**
   * Handle post import migration event.
   *
   * @param \Drupal\migrate\Event\MigrateImportEvent $event
   *   The import event object.
   */
  public function onMigratePostImport(MigrateImportEvent $event) {
    $event_id = $event->getMigration()->getBaseId();
    // Only process nodes, nothing else. Use migration prefix
    // to identify node migrations.
    if (preg_match('/^' . $this->migrationPrefix . '/', $event_id)) {
      $content_type = substr($event_id, strlen($this->migrationPrefix));
      $this->migrationProcessors->updatePublishStatus($this->logger, $content_type);
    }
  }

}
