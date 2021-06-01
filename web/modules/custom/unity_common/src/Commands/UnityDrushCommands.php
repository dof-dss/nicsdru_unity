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

  /**
   * Drush command replace SITE_FOLDER_NAME and STARTERKIT text in a unity site
   * sub-theme.
   *
   * @param string $site_folder_name
   *   Argument for user to enter the site directory they wish to target.
   * @param string $starterkit
   *   Argument for user to add the new name of the sub-theme, replacing
   *   STARTERKIT.
   *
   * @command update-starterkit-text
   * @aliases upst
   * @usage update-starterkit-text --site_folder_name --new_theme_name
   */
  public function changeText($site_folder_name = '', $starterkit = '') {
    // Get the site folder name entered as the 1st parameter by the user.
    $site_path = getcwd() . '/sites/' . $site_folder_name;

    // Rename the STARTERKIT directory to the new sub-theme name entered by the user as the 2nd parameter.
    rename($site_path . '/themes/STARTERKIT', $site_path . '/themes/' . $starterkit);
    $new_theme_path = $site_path . '/themes/' . $starterkit;

    // Change the theme file names.
    $theme_file_names = glob($new_theme_path . '/*');
    foreach ($theme_file_names as $theme_file_name) {
      $new_file_name = str_replace('STARTERKIT', $starterkit, $theme_file_name);
      rename($theme_file_name, $new_file_name);
    }

    // Loop through the new theme directories and update any STARTERKIT and SITE_DIRECTORY_TEXT text.
    $site_directories = [
      glob($new_theme_path . '/config/*'),
      glob($new_theme_path . '/*'),
    ];
    foreach ($site_directories as $site_directory) {
      foreach ($site_directory as $file) {
        $file_contents = file_get_contents($file);
        $strings = ['STARTERKIT',
          'SITE_DIRECTORY_NAME'
        ];
        $string_replacements = [$starterkit,
          $site_folder_name
        ];
        $file_contents = str_replace($strings, $string_replacements, $file_contents);
        if (!is_dir($file)) {
          file_put_contents($file, $file_contents);
        }
      }
    }
  }

}
