<?php

// Configure file paths.
if (!isset($settings['file_public_path'])) {
  $settings['file_public_path'] = 'files/' . $subsite_id;
}

$databases['default']['default'] = [
  'database'  => getenv('DB_NAME'),
  'username'  => getenv('DB_USER'),
  'password'  => getenv('DB_PASS'),
  'prefix'    => getenv('DB_PREFIX'),
  'host'      => $subsite_id,
  'port'      => getenv('DB_PORT'),
  'namespace' => getenv('DB_NAMESPACE'),
  'driver'    => getenv('DB_DRIVER'),
];
