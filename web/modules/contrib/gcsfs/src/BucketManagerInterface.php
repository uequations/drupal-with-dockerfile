<?php

namespace Drupal\gcsfs;

/**
 * Bucket manager interface.
 */
interface BucketManagerInterface {

  /**
   * Check to see if the bucket exists.
   *
   * @return bool
   *   Whether the bucket exists or not.
   */
  public function bucketExists();

  /**
   * Check to see if the bucket is writable.
   *
   * @return bool
   *   Whether the bucket is writeable or not.
   */
  public function bucketWritable();

  /**
   * Compose multiple objects into one.
   *
   * @param array $source
   *   A list of source object names.
   * @param string $name
   *   The name of the destination.
   * @param array $options
   *   The options to pass with the request.
   *
   * @return bool|\Google\Cloud\Storage\StorageObject
   *   FALSE if the object was not composed, otherwise a StorageObject object.
   *
   * @see \Google\Cloud\Storage\Bucket::compose().
   */
  public function composeObjects(array $sources, string $name, array $options = []);

  /**
   * Create an object in the bucket.
   *
   * @param string|resource|StreamInterface|null $data
   *   The data to be uploaded.
   * @param array $options
   *   The options to pass with the data.
   *
   * @return bool|\Google\Cloud\Storage\StorageObject
   *   FALSE if the object was not created, otherwise a StorageObject object.
   *
   * @see \Google\Cloud\Storage\Bucket::upload().
   */
  public function createObject($data, array $options = []);

  /**
   * Delete an object from the bucket.
   *
   * @param string $path
   *   The path of the object to remove.
   *
   * @return bool
   *   Whether the object was deleted or not.
   */
  public function deleteObject(string $path);

  /**
   * Get the bucket name.
   *
   * @return string
   *   The bucket name.
   */
  public function getBucketName();

  /**
   * Get the bucket info.
   *
   * @return array
   *   The bucket info.
   */
  public function getBucketInfo();

  /**
   * Get streamable uploader from the bucket.
   *
   * @param string|resource|StreamInterface|null $data
   *   The stream.
   * @param array $options
   *   The options to pass with the stream.
   *
   * @return \Google\Cloud\Core\Upload\StreamableUploader|NULL
   *   The StreamableUploader object or NULL on failure.
   */
  public function getBucketStreamableUploader($data, array $options = []);

  /**
   * Get an object from the bucket.
   *
   * @param string $path
   *   The path of the object to remove.
   *
   * @return \Google\Cloud\Storage\StorageObject|NULL
   *   The StorageObject object or NULL on failure.
   */
  public function getObject(string $path);

  /**
   * Get a list of objects from the bucket.
   *
   * @param array $options
   *   The options that determine which objects to return.
   *
   * @return \Google\Cloud\Storage\ObjectIterator<\Google\Cloud\Storage\StorageObject>
   *   An iterator of StorageObject objects.
   *
   * @see \Google\Cloud\Storage\Bucket::objects().
   */
  public function getObjects(array $options = []);

}
