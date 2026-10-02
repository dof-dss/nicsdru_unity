<?php

/**
 * @file
 * Deploy hooks for the UREGNI consultations module.
 */

/**
 * Migrates block permissions and removes stale module state.
 */
function uregni_consultations_deploy_remove_block_content_permissions(array &$sandbox): string {
  $role_storage = \Drupal::entityTypeManager()->getStorage('user_role');
  $migrated_permissions = 0;

  // Core uses "edit" where block_content_permissions used "update". The
  // create and delete permission machine names are unchanged in core.
  foreach ($role_storage->loadMultiple() as $role) {
    $role_changed = FALSE;

    foreach ($role->getPermissions() as $permission) {
      if (preg_match('/^update any (.+) block content$/', $permission, $matches)) {
        $role->revokePermission($permission);
        $role->grantPermission("edit any {$matches[1]} block content");
        $migrated_permissions++;
        $role_changed = TRUE;
      }
    }

    // Supervisors manage reusable blocks and therefore need access to core's
    // Content Blocks overview page.
    if ($role->id() === 'supervisor_user' && !$role->hasPermission('access block library')) {
      $role->grantPermission('access block library');
      $migrated_permissions++;
      $role_changed = TRUE;
    }

    if ($role_changed) {
      $role->save();
    }
  }

  $extension_config = \Drupal::configFactory()->getEditable('core.extension');

  if ($extension_config->get('module.block_content_permissions') !== NULL) {
    $extension_config
      ->clear('module.block_content_permissions')
      ->save(TRUE);
  }

  \Drupal::keyValue('system.schema')->delete('block_content_permissions');

  return t('Migrated @count block content permissions and removed stale block_content_permissions module state.', [
    '@count' => $migrated_permissions,
  ]);
}
