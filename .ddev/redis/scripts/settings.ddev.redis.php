<?php

/**
 * @file
 * Project-owned local Redis settings for regular and multisite installs.
 */

use Drupal\Core\Installer\InstallerKernel;

if (!InstallerKernel::installationAttempted() && extension_loaded('redis')) {
  // Register Redis classes before the module is enabled during bootstrap.
  $class_loader->addPsr4('Drupal\\redis\\', $app_root . '/modules/contrib/redis/src');
  $settings['redis.connection']['interface'] = 'PhpRedis';
  if (!empty($subsite_id)) {
    $settings['cache_prefix'] = $subsite_id . '_';
  }
  // Set Redis as the default backend for any cache bin not otherwise specified.
  $settings['cache']['default'] = 'cache.backend.redis';
  $settings['redis.connection']['host'] = 'redis';
  $settings['redis.connection']['port'] = 6379;

  // Apply changes to the container configuration to better leverage Redis.
  // This includes using Redis for the lock and flood control systems, as well
  // as the cache tag checksum. Alternatively, copy the contents of that file
  // to your project-specific services.yml file, modify as appropriate, and
  // remove this line.
  // Match hosted overrides, including any project-specific flood policy.
  $settings['container_yamls'][] = file_exists($app_root . '/sites/redis.services.yml')
    ? 'sites/redis.services.yml'
    : 'modules/contrib/redis/example.services.yml';

  // Allow the services to work before the Redis module itself is enabled.
  $settings['container_yamls'][] = 'modules/contrib/redis/redis.services.yml';

  // Use redis for container cache.
  // The container cache is used to load the container definition itself, and
  // thus any configuration stored in the container itself is not available
  // yet. These lines force the container cache to use Redis rather than the
  // default SQL cache.
  $settings['bootstrap_container_definition'] = [
    'parameters' => [],
    'services' => [
      'redis.factory' => [
        'class' => 'Drupal\redis\ClientFactory',
      ],
      'cache.backend.redis' => [
        'class' => 'Drupal\redis\Cache\CacheBackendFactory',
        'arguments' => ['@redis.factory', '@cache_tags_provider.container', '@serialization.phpserialize'],
      ],
      'cache.container' => [
        'class' => '\Drupal\redis\Cache\PhpRedis',
        'factory' => ['@cache.backend.redis', 'get'],
        'arguments' => ['container'],
      ],
      'cache_tags_provider.container' => [
        'class' => 'Drupal\redis\Cache\RedisCacheTagsChecksum',
        'arguments' => ['@redis.factory'],
      ],
      'serialization.phpserialize' => [
        'class' => 'Drupal\Component\Serialization\PhpSerialize',
      ],
    ],
  ];
}
