<?php

namespace Drupal\unity_file_migrations\EventSubscriber;

use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;
use Drupal\unity_file_migrations\MigrationProcessors;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\Core\Logger\LoggerChannelFactory;

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
   * PostMigrationSubscriber constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactory $logger
   *   Drupal logger.
   */
  public function __construct(LoggerChannelFactory $logger) {
    $this->logger = $logger->get('unity_file_migrations');
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
    // Only process nodes, nothing else.
    if (preg_match('/^upgrade_d7_node_/', $event_id)) {
      $content_type = substr($event_id, 16);
      MigrationProcessors::updatePublishStatus($this->logger, $content_type);
    }
  }

}
