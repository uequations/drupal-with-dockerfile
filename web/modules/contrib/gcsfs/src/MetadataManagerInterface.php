<?php

namespace Drupal\gcsfs;

/**
 * Metadata manager interface.
 */
interface MetadataManagerInterface {

  /**
   * Cache bin.
   *
   * @var string
   */
  const CACHE_BIN = 'default';

  /**
   * Cache prefix.
   *
   * @var string
   */
  const CACHE_PREFIX = 'gcsfs:uri:';

  /**
   * Cache tag.
   *
   * @var string
   */
  const CACHE_TAG = 'gcsfs';

  /**
   * Maximum URI length.
   *
   * The maximum length of a URI that can be stored in the database. Anything
   * longer will throw an error.
   *
   * @var int
   */
  const MAXIMUM_URI_LENGTH = 255;

  /**
   * Delete a metadata record.
   *
   * @param string $path
   *   The path of the object.
   */
  public function delete(string $path);

  /**
   * Get a metadata record.
   *
   * @param string $path
   *   The path of the object.
   *
   * @return array|bool
   *   An array of metadata about the object or FALSE if the object does not exist.
   */
  public function get(string $path);

  /**
   * Set a metadata record.
   *
   * @param string $path
   *   The path of the object.
   * @param int $filesize
   *   The filesize of the object.
   * @param bool $directory
   *   Object is a directory or not.
   */
  public function set(string $path, int $filesize, bool $directory = FALSE);

}
