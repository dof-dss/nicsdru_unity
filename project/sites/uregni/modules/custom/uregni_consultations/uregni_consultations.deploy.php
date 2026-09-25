<?php

/**
 * @file
 * Deploy hooks for the UREGNI consultations module.
 */

/**
 * Removes stale state for the unavailable block_content_permissions module.
 */
function uregni_consultations_deploy_remove_block_content_permissions(array &$sandbox): string {
  $extension_config = \Drupal::configFactory()->getEditable('core.extension');

  if ($extension_config->get('module.block_content_permissions') !== NULL) {
    $extension_config
      ->clear('module.block_content_permissions')
      ->save(TRUE);
  }

  \Drupal::keyValue('system.schema')->delete('block_content_permissions');

  return t('Removed stale block_content_permissions module state.');
}
