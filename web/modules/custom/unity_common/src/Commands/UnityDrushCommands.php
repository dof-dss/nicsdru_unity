<?php

namespace Drupal\unity_common\Commands;

use Drush\Commands\DrushCommands;
use Drupal\structure_sync\StructureSyncHelper;

/**
 * Drush custom commands.
 */
class UnityDrushCommands extends DrushCommands {

  /**
   * Drush command import blocks, taxonomies and menus  using structure_sync.
   *
   * @param string $option
   *   Argument to select 'safe', 'full' or 'force'.
   *
   * @command import-all-if-installed
   */
  public function importAllIfInstalled($option = 'safe') {
    // Only import if the structure_sync module is installed.
    if (\Drupal::moduleHandler()->moduleExists('structure_sync')) {
      $config = \Drupal::config('structure_sync.data');
      // Import blocks if necessary.
      $blocks = $config->get('blocks');
      if (!empty($blocks)) {
        StructureSyncHelper::importCustomBlocks([
          'style' => $option,
          'drush' => TRUE,
        ]);
      }
      // Import taxonomies if necessary.
      $taxonomies = $config->get('taxonomies');
      if (!empty($taxonomies)) {
        StructureSyncHelper::importTaxonomies([
          'style' => $option,
          'drush' => TRUE,
        ]);
      }
      // Import menus if necessary.
      $menus = $config->get('menus');
      if (!empty($menus)) {
        StructureSyncHelper::importMenuLinks([
          'style' => $option,
          'drush' => TRUE,
        ]);
      }
    }
  }

}
