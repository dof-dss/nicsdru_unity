<?php

// Configure file paths.
if (!isset($settings['file_public_path'])) {
  $settings['file_public_path'] = 'files/' . $subsite_id;
}

$databases['default']['default'] = [
  'database'  => getenv('UREGNI_DB_NAME'),
  'username'  => getenv('UREGNI_DB_USER'),
  'password'  => getenv('UREGNI_DB_PASS'),
  'prefix'    => getenv('UREGNI_DB_PREFIX'),
  'host'      => getenv('UREGNI_DB_HOST'),
  'port'      => getenv('UREGNI_DB_PORT'),
  'namespace' => getenv('UREGNI_DB_NAMESPACE'),
  'driver'    => getenv('UREGNI_DB_DRIVER'),
];
