<?php

namespace Drupal\gcsfs;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Storage\Bucket;
use Google\Cloud\Storage\StorageClient;

/**
 * Bucket manager class.
 */
class BucketManager implements BucketManagerInterface {
  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * Google Cloud Storage bucket.
   *
   * @var \Google\Cloud\Storage\Bucket
   */
  protected Bucket $bucket;

  /**
   * Google Cloud Storage Client.
   *
   * @var \Google\Cloud\Storage\StorageClient
   */
  protected StorageClient $client;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * Config is valid.
   *
   * @var bool
   */
  protected bool $configValid = FALSE;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * Logger factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected LoggerChannelFactoryInterface $logger;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManagerInterface $entity_type_manager, LoggerChannelFactoryInterface $logger) {
    $this->configFactory = $config_factory;
    $this->entityTypeManager = $entity_type_manager;
    $this->logger = $logger;

    /** @var \Drupal\Core\Config\ImmutableConfig $config */
    $config = $this->configFactory->get('gcsfs.config');

    $credentials = $config->get('credentials');
    if ($credentials) {
      // Create the storage client.
      $this->client = new StorageClient(
        [
          'keyFile' => json_decode($credentials, TRUE),
        ]
      );

      // All operations happen on the bucket.
      $bucket_name = $config->get('bucket_name');
      if ($bucket_name) {
        $this->bucket = $this->client->bucket($bucket_name);
        $this->configValid = TRUE;
      }
    }

    if (!$this->configValid) {
      $this->messenger()->addError($this->t('The Google Cloud Storage File System module is not configured.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function bucketExists() {
    if ($this->configValid) {
      $exists = &drupal_static(__METHOD__);

      if (!isset($exists)) {
        $exists = FALSE;
        try {
          $exists = $this->bucket->exists();
        }
        catch (ServiceException $e) {
          $this->handleException($e);
        }
      }

      return $exists;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function bucketWritable() {
    if ($this->configValid) {
      return $this->bucket->isWritable();
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function composeObjects(array $sources, string $name, array $options = []) {
    $object = FALSE;

    if ($this->configValid) {
      try {
        $object = $this->bucket->compose($sources, $name, $options);
      }
      catch (ServiceException $e) {
        $this->handleException($e);
      }
    }

    return $object;
  }

  /**
   * {@inheritdoc}
   */
  public function createObject($data, array $options = []) {
    $object = FALSE;

    if ($this->configValid) {
      try {
        $object = $this->bucket->upload($data, $options);
      }
      catch (ServiceException $e) {
        $this->handleException($e);
      }
    }

    return $object;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteObject(string $path) {
    $deleted = FALSE;

    if ($this->configValid) {
      try {
        /** @var \Google\Cloud\Storage\StorageObject $object */
        $object = $this->getObject($path);
        $object->delete();
        $deleted = !$object->exists();
      }
      catch (ServiceException $e) {
        $this->handleException($e);
      }
    }

    return $deleted;
  }

  /**
   * {@inheritdoc}
   */
  public function getBucketInfo() {
    $output = [];

    if ($this->configValid) {
      try {
        $output = $this->bucket->info();
      }
      catch (ServiceException $e) {
        $this->handleException($e);
      }
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getBucketName() {
    return $this->configFactory->get('gcsfs.config')->get('bucket_name');
  }

  /**
   * {@inheritdoc}
   */
  public function getBucketStreamableUploader($data, array $options = []) {
    $output = NULL;

    if ($this->configValid) {
      try {
        $output = $this->bucket->getStreamableUploader($data, $options);
      }
      catch (ServiceException $e) {
        $this->handleException($e);
      }
    }

    return $output;
  }

  /**
   * {@inheritdoc}
   */
  public function getObject(string $path) {
    if ($this->configValid) {
      return $this->bucket->object($path);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getObjects(array $options = []) {
    $objects = NULL;

    if ($this->configValid) {
      try {
        $objects = $this->bucket->objects($options);
      }
      catch (ServiceException $e) {
        $this->handleException($e);
      }
    }

    return $objects;
  }

  /**
   * {@inheritdoc}
   */
  protected function handleException(ServiceException $exception) {
    $message = json_decode($exception->getMessage());

    $this->messenger()->addError(
      $this->t(
        'Response Code @code - @message',
        [
          '@code' => $message->error->code,
          '@message' => $message->error->message,
        ]
      )
    );

    $this->logger->get('gcsfs')->error(
      'Response Code @code - @message',
      [
        '@code' => $message->error->code,
        '@message' => $message->error->message,
      ]
    );
  }
}
