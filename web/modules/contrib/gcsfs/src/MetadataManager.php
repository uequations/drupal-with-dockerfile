<?php

namespace Drupal\gcsfs;

use Drupal\gcsfs\StreamWrapper\GoogleCloudStorage;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Lock\LockBackendInterface;

/**
 * Metadata manager class.
 */
class MetadataManager implements MetadataManagerInterface {

  /**
   * Cache factory.
   *
   * @var \Drupal\Core\Cache\CacheFactoryInterface
   */
  protected CacheFactoryInterface $cacheFactory;

  /**
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected Connection $connection;

  /**
   * Lock service.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected LockBackendInterface $lock;

  /**
   * Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected TimeInterface $time;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Cache\CacheFactoryInterface $cache_factory
   *   The cache factory.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Component\Datetime\TimeInterface $time
   *   The time service.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock service.
   */
  public function __construct(CacheFactoryInterface $cache_factory, Connection $connection, TimeInterface $time, LockBackendInterface $lock) {
    $this->cacheFactory = $cache_factory;
    $this->connection = $connection;
    $this->time = $time;
    $this->lock = $lock;
  }

  /**
   * {@inheritdoc}
   */
  public function delete(string $path) {
    $metadata = &drupal_static(__CLASS__);

    // Remove data from static cache (if it exists).
    if (isset($metadata[$path])) {
      unset($metadata[$path]);
    }

    // Remove data from cache bin.
    $cid = self::CACHE_PREFIX . $path;
    /** @var \Drupal\Core\Cache\CacheBackendInterface $bin */
    $bin = $this->cacheFactory->get(self::CACHE_BIN);
    $bin->delete($cid);

    // Remove from the database.
    $this->connection
      ->delete('gcsfs_object_metadata')
      ->condition('path', $path)
      ->execute();

    // Clear the stat cache for PHP.
    clearstatcache(TRUE, GoogleCloudStorage::STREAM_PROTOCOL . '://' . $path);
  }

  /**
   * {@inheritdoc}
   */
  public function get(string $path) {
    $metadata = &drupal_static(__CLASS__);

    if (empty($metadata[$path])) {
      $cid = self::CACHE_PREFIX . $path;
      /** @var \Drupal\Core\Cache\CacheBackendInterface $bin */
      $bin = $this->cacheFactory->get(self::CACHE_BIN);
      $cache = $bin->get($cid);
      if ($cache) {
        $metadata[$path] = $cache->data;
      }
      else {

        // Lock acquired, populate the cache data from the database.
        if ($this->lock->acquire($cid, 1)) {
          $metadata[$path] = $this->connection
            ->select('gcsfs_object_metadata', 'm')
            ->fields('m')
            ->condition('path', $path)
            ->execute()
            ->fetchAssoc();

          $bin->set($cid, $metadata[$path], Cache::PERMANENT, [self::CACHE_TAG]);
          $this->lock->release($cid);
        }

        // Lock is not acquired, wait for another process to build the cache.
        else {
          $this->lock->wait($cid);
          $this->get($path);
        }
      }
    }

    return $metadata[$path];
  }

  /**
   * {@inheritdoc}
   */
  public function set(string $path, int $filesize, bool $directory = FALSE) {

    // Remove the metadata before setting it, in case it already exists.
    $this->delete($path);

    // The array of metadata that will be stored in the static cache, the cache
    // bin and the database.
    $object_metadata = [
      'path' => $path,
      'file_size' => $filesize,
      'directory' => (int) $directory,
      'created' => $this->time->getCurrentTime(),
    ];

    // Store in the database.
    $this->connection
      ->insert('gcsfs_object_metadata')
      ->fields($object_metadata)
      ->execute();
    
    // Store in the cache bin.
    $cid = self::CACHE_PREFIX . $path;
    /** @var \Drupal\Core\Cache\CacheBackendInterface $bin */
    $bin = $this->cacheFactory->get(self::CACHE_BIN);
    $bin->set($cid, $object_metadata, Cache::PERMANENT, [self::CACHE_TAG]);

    // Store in the static cache.
    $metadata = &drupal_static(__CLASS__);
    $metadata[$path] = $object_metadata;

    // Clear the stat cache for PHP.
    clearstatcache(TRUE, GoogleCloudStorage::STREAM_PROTOCOL . '://' . $path);
  }

}
