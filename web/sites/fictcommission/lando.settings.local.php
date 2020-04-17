<?php

// Configure file paths.
if (!isset($settings['file_public_path'])) {
  $settings['file_public_path'] = 'files/' . $subsite_id;
}

$databases['default']['default'] = [
  'database'  => getenv('FICTCOMMISSION_DB_NAME'),
  'username'  => getenv('FICTCOMMISSION_DB_USER'),
  'password'  => getenv('FICTCOMMISSION_DB_PASS'),
  'prefix'    => getenv('FICTCOMMISSION_DB_PREFIX'),
  'host'      => getenv('FICTCOMMISSION_DB_HOST'),
  'port'      => getenv('FICTCOMMISSION_DB_PORT'),
  'namespace' => getenv('FICTCOMMISSION_DB_NAMESPACE'),
  'driver'    => getenv('FICTCOMMISSION_DB_DRIVER'),
];
