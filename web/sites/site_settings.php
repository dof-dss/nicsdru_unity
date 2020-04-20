<?php

// Default Drupal 8 settings.
//
// These are already explained with detailed comments in Drupal's
// default.settings.php file.
//
// See https://api.drupal.org/api/drupal/sites!default!default.settings.php/8
$databases = [];
$settings['update_free_access'] = FALSE;
$settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';
$settings['file_scan_ignore_directories'] = [
  'node_modules',
  'bower_components',
];

// Site hash salt.
$settings['hash_salt'] = getenv('HASH_SALT');

// Temp directory.
$settings["file_temp_path"] = getenv('FILE_TEMP_PATH') ?? '/tmp';

// Set config split environment; environment specific values is set near the end of this file.
$config['config_split.config_split.local']['status'] = FALSE;
$config['config_split.config_split.development']['status'] = FALSE;
$config['config_split.config_split.production']['status'] = FALSE;

// Config readonly settings; default to active if not specified.
$settings['config_readonly'] = !empty(getenv('CONFIG_READONLY')) ? getenv('CONFIG_READONLY') : 1;

// Permit changes via command line.
if (PHP_SAPI === 'cli') {
  $settings['config_readonly'] = 0;
}

// Configuration that is allowed to be changed in readonly environments.
$settings['config_readonly_whitelist_patterns'] = [
  'system.site',
];

// Geocoder API key.
$config['geolocation.settings']['google_map_api_key'] = getenv('GOOGLE_MAP_API_KEY');

// Environment indicator defaults.
$env_colour = !empty(getenv('SIMPLEI_ENV_COLOR')) ? getenv('SIMPLEI_ENV_COLOR') : '#000000';;
$env_name = !empty(getenv('SIMPLEI_ENV_NAME')) ? getenv('SIMPLEI_ENV_NAME') : getenv('PLATFORM_BRANCH');

// Extract the directory name for multi site identification.
$subsite_id = basename(__DIR__);

// If we're running on platform.sh, check for and load relevant settings.
if (!empty(getenv('PLATFORM_BRANCH'))) {

  if (file_exists($app_root . '/' . $site_path . '/../settings.platformsh.php')) {
    include $app_root . '/' . $site_path . '/../settings.platformsh.php';
  }

  // Environment specific settings and services.
  switch (getenv('PLATFORM_BRANCH')) {
    case 'master':
      // De-facto production settings.
      $config['config_split.config_split.production']['status'] = TRUE;
      $settings['container_yamls'][] = $app_root . '/' . $site_path . '/services.yml';
      break;

    case (stripos(getenv('PLATFORM_BRANCH'), 'D8UN-qa') !== FALSE):
      // QA environment config adjustments.
      $env_colour = '#e56716';
      break;

    default:
      // Default to use development settings/services for general platform.sh environments.
      $config['config_split.config_split.development']['status'] = TRUE;
      $settings['container_yamls'][] = $app_root . '/' . $site_path . '/../development.services.yml';
      include $app_root . '/' . $site_path . '/../settings.development.php';
  }
}

$settings['simple_environment_indicator'] = sprintf('%s %s', $env_colour, $env_name);

// Set up a config sync directory.
//
// This is defined inside the read-only "config" directory, deployed via Git.
$settings['config_sync_directory'] = '../config/sync/' . $subsite_id;

// Configure file paths.
if (!isset($settings['file_public_path'])) {
  $settings['file_public_path'] = 'files/' . $subsite_id;
}

$databases['default']['default'] = [
  'database' => getenv('DB_NAME'),
  'username' => getenv('DB_USER'),
  'password' => getenv('DB_PASS'),
  'prefix' => getenv('DB_PREFIX'),
  'host' => $subsite_id,
  'port' => getenv('DB_PORT'),
  'namespace' => getenv('DB_NAMESPACE'),
  'driver' => getenv('DB_DRIVER'),
];

// Local settings. These come last so that they can override anything.
if (file_exists($app_root . '/' . $site_path . '/settings.local.php')) {
  include $app_root . '/' . $site_path . '/settings.local.php';
}


