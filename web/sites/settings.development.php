<?php

/**
 * @file
 * Default Drupal 8 settings.
 *
 * These are already explained with detailed comments in Drupal's
 * default.settings.php file.
 *
 * See https://api.drupal.org/api/drupal/sites!default!default.settings.php/8
 */

$settings['container_yamls'][] = $app_root . '/' . $site_path . '/development.services.yml';

// Set config split environment.
$config['config_split.config_split.local']['status'] = FALSE;
$config['config_split.config_split.hosted']['status'] = TRUE;

// Environment indicator config.
$settings['simple_environment_indicator'] = sprintf('%s %s', getenv('SIMPLEI_ENV_COLOUR'), getenv('SIMPLEI_ENV_NAME'));
