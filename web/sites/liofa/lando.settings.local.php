<?php

// Configure file paths.
if (!isset($settings['file_public_path'])) {
  $settings['file_public_path'] = 'files/' . $subsite_id;
}

$databases['default']['default'] = [
  'database'  => getenv('LIOFA_DB_NAME'),
  'username'  => getenv('LIOFA_DB_USER'),
  'password'  => getenv('LIOFA_DB_PASS'),
  'prefix'    => getenv('LIOFA_DB_PREFIX'),
  'host'      => getenv('LIOFA_DB_HOST'),
  'port'      => getenv('LIOFA_DB_PORT'),
  'namespace' => getenv('LIOFA_DB_NAMESPACE'),
  'driver'    => getenv('LIOFA_DB_DRIVER'),
];
