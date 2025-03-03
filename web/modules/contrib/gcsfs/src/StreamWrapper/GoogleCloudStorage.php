<?php

namespace Drupal\gcsfs\StreamWrapper;

use Drupal\gcsfs\BucketManagerInterface;
use Drupal\gcsfs\MetadataManagerInterface;
use Drupal\gcsfs\Utility\PathResolver;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Google\Cloud\Core\Exception\NotFoundException;
use Google\Cloud\Core\Exception\ServiceException;
use Google\Cloud\Storage\ObjectIterator;
use Google\Cloud\Storage\ReadStream;
use Google\Cloud\Storage\SigningHelper;
use Google\Cloud\Storage\StorageObject;
use Google\Cloud\Storage\WriteStream;
use GuzzleHttp\Psr7\CachingStream;
use Psr\Http\Message\StreamInterface;

/**
 * Google Cloud Storage (gs://) stream wrapper class.
 */
class GoogleCloudStorage implements StreamWrapperInterface {
  use StringTranslationTrait;

  /**
   * Directory readable mode.
   *
   * 40444 in octal.
   *
   * @var int
   */
  const DIRECTORY_READABLE_MODE = 16676;

  /**
   * Directory writable mode.
   *
   * 40777 in octal.
   *
   * @var int
   */
  const DIRECTORY_WRITABLE_MODE = 16895;

  /**
   * File readable mode.
   *
   * 100444 in octal.
   *
   * @var int
   */
  const FILE_READABLE_MODE = 33060;

  /**
   * File writeable mode.
   *
   * 100666 in octal.
   *
   * @var int
   */
  const FILE_WRITABLE_MODE = 33206;

  /**
   * Stream protocol.
   *
   * @var string
   */
  const STREAM_PROTOCOL = 'gs';

  /**
   * Tail name suffix.
   *
   * @var string
   */
  const TAIL_NAME_SUFFIX = '~';

  /**
   * Bucket manager service.
   *
   * @var \Drupal\gcsfs\BucketManagerInterface
   */
  protected BucketManagerInterface $bucketManager;

  /**
   * Stream Context.
   *
   * @var resource|null
   */
  public $context;

  /**
   * Stream is composing.
   *
   * TRUE is writing the "tail" object, next fflush() or fclose() will compose.
   *
   * @var bool
   */
  protected bool $composing = FALSE;

  /**
   * File content type.
   *
   * @var string
   */
  protected ?string $contentType;

  /**
   * Stream is dirty.
   *
   * TRUE if data has been written to the stream.
   *
   * @var bool
   */
  protected bool $dirty = FALSE;

  /**
   * Stream is Flushing.
   *
   * TRUE if fflush() will flush output buffer and redirect output to the "tail" object.
   * 
   * @var bool
   */
  protected bool $flushing = FALSE;

  /**
   * Iterator.
   * 
   * The iterator used by opendir(), readdir(), scandir() and rewinddir().
   *
   * @var \Google\Cloud\Storage\ObjectIterator|NULL
   */
  protected ?ObjectIterator $iterator;

  /**
   * Metadata manager.
   *
   * @var \Drupal\gcsfs\MetadataManagerInterface
   */
  protected MetadataManagerInterface $metadataManager;

  /**
   * Stream options.
   *
   * @var array
   */
  protected array $options = [];

  /**
   * Stream.
   *
   * @var \Psr\Http\Message\StreamInterface
   */
  protected StreamInterface $stream;

  /**
   * Stream wrappeer manager.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */
  protected StreamWrapperManager $streamWrapperManager;

  /**
   * Instance URI (stream).
   *
   * A stream is referenced as "scheme://target".
   *
   * @var string
   */
  protected string $uri;

  /**
   * Constructor.
   */
  public function __construct() {
    $this->bucketManager = \Drupal::service('gcsfs.bucket_manager');
    $this->metadataManager = \Drupal::service('gcsfs.metadata_manager');
    $this->streamWrapperManager = \Drupal::service('stream_wrapper_manager');
    $this->context = stream_context_get_default();
  }

  /**
   * {@inheritdoc}
   */
  public static function getType() {
    return StreamWrapperInterface::NORMAL;
  }

  /**
   * {@inheritdoc}
   */
  public function dirname($uri = NULL) {
    if (isset($uri)) {
      $this->setUri($uri);
    }

    $scheme = PathResolver::getScheme($this->uri);
    $dirname = dirname(PathResolver::getTarget($this->uri));

    // When the dirname() call above is given '$scheme://', it returns '.'.
    // But '$scheme://.' is an invalid uri, so return "$scheme://" instead.
    if ($dirname == '.') {
      $dirname = '';
    }

    return "$scheme://$dirname";
  }

  /**
   * {@inheritdoc}
   */
  public function dir_closedir() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function dir_opendir($path, $options = NULL) {
    $this->setUri($path);

    return $this->dir_rewinddir();
  }

  /**
   * {@inheritdoc}
   */
  public function dir_readdir() {
    $object = $this->iterator->current();
    if ($object) {
      $this->iterator->next();

      return $object->name();
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function dir_rewinddir() {
    if ($this->bucketManager->bucketExists()) {
      $this->iterator = $this->bucketManager->getObjects(
        [
          'prefix' => $this->uri . '/',
          'fields' => 'items/name,nextPageToken',
        ]
      );

      if (!isset($this->iterator)) {
        return FALSE;
      }
    }
    else {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Stream wrapper service for the Google Cloud Storage StreamWrapper class.');
  }

  /**
   * Gets the path that the wrapper is responsible for.
   *
   * This function isn't part of DrupalStreamWrapperInterface, but the rest
   * of Drupal calls it as if it were, so we need to define it.
   *
   * @return string
   *   The empty string. Since this is a remote stream wrapper,
   *   it has no directory path.
   *
   * @see \Drupal\Core\File\LocalStream::getDirectoryPath()
   */
  public function getDirectoryPath() {
    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function getExternalUrl() {
    $target = PathResolver::getTarget($this->uri);

    // Convert image style uri's to local URL's which allows for processing when
    // the URL is accessed. If the uri exists in metadata then it's been
    // processed already and this can be skipped.
    if (preg_match('//', $target, $matches) && !$this->metadataManager->get($target)) {
      /** @var \Drupal\Core\StreamWrapper\PublicStream $public */
      $public = $this->streamWrapperManager->getViaScheme('public');
      $base = $public->getDirectoryPath();
      $url = Url::fromUserInput('/' . $base . '/' . $target);
      $url->setAbsolute();

      return $url->toString();
    }

    return 'https://' . SigningHelper::DEFAULT_DOWNLOAD_HOST . '/' . $this->bucketManager->getBucketName() . '/' . $target;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->t('Google Cloud Storage File System');
  }

  /**
   * {@inheritdoc}
   */
  public function getUri() {
    return $this->uri;
  }

  /**
   * {@inheritdoc}
   */
  public function mkdir($uri, $mode, $options) {
    if ($this->bucketManager->bucketExists()) {
      $this->setUri($uri);
      $target = PathResolver::getTarget($this->uri);

      // Don't try to make a directory with no name, consider successful.
      if ($target == '') {
        return TRUE;
      }

      if (mb_strlen($target) > MetadataManagerInterface::MAXIMUM_URI_LENGTH) {
        return FALSE;
      }
      
      // If the bucket has uniform bucket level access enabled, don't set
      // ACL's.
      $bucket_info = $this->bucketManager->getBucketInfo();
      $ubl_enabled = $bucket_info['iamConfiguration']['uniformBucketLevelAccess'] ?? FALSE;
      $options = [];
      if (!$ubl_enabled) {
        $options = [
          'predefinedAcl' => $this->determineAclFromMode($mode),
        ];
      }

      // Create each folder explcitly because Google Cloud Storage will do
      // this implicitly and will not report parent directories as objects. Do
      // not create directories that already exist (according to metadata).
      $target_parts = explode('/', $target);
      $target = '';
      foreach ($target_parts as $target_part) {
        $target .= $target_part . '/';
        if (!$this->metadataManager->get($target)) {
          $options['name'] = $target;

          // Create the directory. Store metadata with no trailing slash.
          $this->bucketManager->createObject('', $options);
          $this->metadataManager->set(rtrim($target, '/'), 0, TRUE);
        }
      }
    }
    else {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function realpath() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function rename($path_from, $path_to) {
    if ($this->bucketManager->bucketExists()) {
      $target_from = PathResolver::getTarget($path_from);
      $target_to = PathResolver::getTarget($path_to);

      if (mb_strlen($target_to) > MetadataManagerInterface::MAXIMUM_URI_LENGTH) {
        return FALSE;
      }

      // Loop through to rename file and children, if given path is a directory.
      $objects = $this->bucketManager->getObjects(
        [
          'prefix' => $target_from,
        ]
      );

      /** @var \Google\Cloud\Storage\StorageObject $object */
      foreach ($objects as $object) {
        try {
          $old_target = $object->name();
          $new_target = str_replace($target_from, $target_to, $object->name());
          $object->rename($new_target);

          // Handle metadata.
          $metadata = $this->metadataManager->get(rtrim($old_target, '/'));
          $this->metadataManager->delete(rtrim($old_target, '/'));
          $this->metadataManager->set(rtrim($new_target, '/'), $metadata['file_size'], (bool) $metadata['directory']);
        }
        catch (ServiceException $e) {
          return FALSE;
        }
      }
    }
    else {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function rmdir($uri, $options) {
    if ($this->bucketManager->bucketExists()) {
      $this->setUri($uri);
      $target = PathResolver::getTarget($this->uri);

      try {
        if ($this->bucketManager->deleteObject($target . '/')) {
          $this->metadataManager->delete($target);
          return TRUE;
        }
      }
      catch (ServiceException $e) {
        return FALSE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setUri($uri) {
    $this->uri = PathResolver::resolve($uri);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_cast($cast_as) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_close() {
    if ($this->bucketManager->bucketExists()) {
      if (isset($this->stream)) {
        $this->stream->close();

        // Save metadata.
        $target = PathResolver::getTarget($this->uri);
        $object = $this->bucketManager->getObject($target);
        if (isset($object)) {
          try {
            $info = $object->info();
            $this->metadataManager->set($target, $info['size']);
          }
          catch (NotFoundException $e) {
          }
        }
      }

      if ($this->composing) {
        if ($this->dirty) {
          $this->compose();
          $this->dirty = FALSE;
        }
        $this->bucketManager->deleteObject(PathResolver::getTarget($this->uri) . self::TAIL_NAME_SUFFIX);
        $this->composing = FALSE;
      }
    }
  }
  
  /**
   * {@inheritdoc}
   */
  public function stream_eof() {
    return $this->stream->eof();
  }

  /**
   * {@inheritdoc}
   */
  public function stream_flush() {
    if ($this->bucketManager->bucketExists()) {
      if (!$this->flushing) {
        return FALSE;
      }

      if (!$this->dirty) {
        return TRUE;
      }

      if (isset($this->stream)) {
        $this->stream->close();
      }

      if ($this->composing) {
        $this->compose();
      }

      $options = $this->options;
      $this->stream = new WriteStream(null, $options);
      /** @var \Google\Cloud\Core\Upload\StreamableUploader $uploader */
      $uploader = $this->bucketManager->getBucketStreamableUploader(
        $this->stream,
        $options + [
          'name' => PathResolver::getTarget($this->uri) . self::TAIL_NAME_SUFFIX,
        ]
      );
      $this->stream->setUploader($uploader);
      $this->composing = TRUE;
      $this->dirty = FALSE;

      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_lock($operation) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_metadata($uri, $option, $value) {
    if ($option == STREAM_META_TOUCH) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_open($path, $mode, $options, &$opened_path) {
    if ($this->bucketManager->bucketExists()) {
      $this->setUri($path);
      $target = PathResolver::getTarget($this->uri);

      if (mb_strlen($target) > MetadataManagerInterface::MAXIMUM_URI_LENGTH) {
        return FALSE;
      }

      // Strip off 'b' or 't' from the mode.
      $mode = rtrim($mode, 'bt');

      // Build stream options.
      $this->options = [];
      if ($this->context) {
        $context_options = stream_context_get_options($this->context);
        if (isset($context_options[self::STREAM_PROTOCOL])) {
          $this->options = $context_options[self::STREAM_PROTOCOL] ?: [];
        }

        if (isset($this->options['flush'])) {
          $this->flushing = (bool)$this->options['flush'];
          unset($this->options['flush']);
        }
      }

      // Write mode.
      if ($mode == 'w') {
        $this->stream = new WriteStream(NULL, $this->options);
        /** @var \Google\Cloud\Core\Upload\StreamableUploader $uploader */
        $uploader = $this->bucketManager->getBucketStreamableUploader(
          $this->stream,
          $this->options + [
            'name' => $target,
          ]
        );
        $this->stream->setUploader($uploader);
      }

      // Append mode.
      elseif ($mode == 'a') {
        $object = $this->bucketManager->getObject($this->uri);
        if (isset($object)) {
          try {
            $info = $object->info();
            $this->composing = ($info['size'] > 0);
          }
          catch (NotFoundException $e) {
          }
        }

        $this->stream = new WriteStream(NULL, $this->options);
        $name = ($this->composing) ? $target . self::TAIL_NAME_SUFFIX : $target;
        /** @var \Google\Cloud\Core\Upload\StreamableUploader $uploader */
        $uploader = $this->bucketManager->getBucketStreamableUploader(
          $this->stream,
          $this->options + [
            'name' => $name,
          ]
        );
        $this->stream->setUploader($uploader);
      }

      // Read mode.
      elseif ($mode == 'r') {
        $this->options['restOptions']['stream'] = true;
        $object = $this->bucketManager->getObject($target);
        if (isset($object)) {
          try {
            $this->stream = new ReadStream($object->downloadAsStream($this->options));
          }
          catch (NotFoundException $e) {
            return FALSE;
          }

          // Wrap the response in a caching stream to make it seekable.
          if (!$this->stream->isSeekable() && ($options & STREAM_MUST_SEEK)) {
            $this->stream = new CachingStream($this->stream);
          }
        }
      }
      else {
        return FALSE;
      }

      if ($options & STREAM_USE_PATH) {
          $opened_path = $this->uri;
      }

      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_read($count) {
    return $this->stream->read($count);
  }

  /**
   * {@inheritdoc}
   */
  public function stream_seek($offset, $whence = SEEK_SET) {
    if ($this->stream->isSeekable()) {
      $this->stream->seek($offset, $whence);

      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   *
   * \Google\Cloud\Storage\StreamWrapper class defines this with no arguments
   * but \Drupal\Core\StreamWrapper\StreamWrapperInterface interface defines
   * this will all 3 arguments. To make them both happy, adding the args with
   * default values.
   */
  public function stream_set_option($option = NULL, $arg1 = NULL, $arg2 = NULL) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_stat() {
    $mode = $this->stream->isWritable() ? self::FILE_WRITABLE_MODE : self::FILE_READABLE_MODE;
    
    return $this->makeStatArray(
      [
        'mode' => $mode,
        'size' => $this->stream->getSize(),
      ]
    );
  }

  /**
   * {@inheritdoc}
   */
  public function stream_tell() {
    return $this->stream->tell();
  }

  /**
   * {@inheritdoc}
   */
  public function stream_truncate($new_size) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function stream_write($data) {
    $result = $this->stream->write($data);

    $this->dirty = ($this->dirty || (bool)$result);

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function unlink($uri) {
    if ($this->bucketManager->bucketExists()) {
      $this->setUri($uri);
      $target = PathResolver::getTarget($this->uri);

      try {
        if ($this->bucketManager->bucketExists()) {
          if ($this->bucketManager->deleteObject($target)) {
            $this->metadataManager->delete($target);
            return TRUE;
          }
        }
      }
      catch (ServiceException $e) {
        return FALSE;
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function url_stat($uri, $flags) {
    $this->setUri($uri);

    // if directory
    $dir = $this->getDirectoryInfo(PathResolver::getTarget($this->uri));
    if (isset($dir)) {
      return $this->urlStatDirectory($dir);
    }

    return $this->urlStatFile();
  }

  /**
   * Compose.
   *
   * This is a copy of the private function from Google's StreamWrapper class.
   *
   * @see \Google\Cloud\Storage\StreamWrapper::compose().
   */
  protected function compose() {
    if ($this->bucketManager->bucketExists()) {
      $target = PathResolver::getTarget($this->uri);

      if (!isset($this->contentType)) {
        $object = $this->bucketManager->getObject($target);
        if (isset($object)) {
          try {
            $info = $object->info();
            $this->contentType = $info['contentType'] ?: 'application/octet-stream';
          }
          catch (NotFoundException $e) {
          }
        }
      }

      $this->bucketManager->composeObjects(
        [$target, $target . self::TAIL_NAME_SUFFIX],
        $target,
        ['destination' => ['contentType' => $this->contentType]]
      );
    }
  }

  /**
   * Determine ACL from mode.
   *
   * Helper for determining which predefinedAcl to use given a mode. This is a
   * copy of the private function from Google's StreamWrapper class.
   *
   * @param int $mode
   *   Decimal representation of the file system permissions
   *
   * @return string
   *   The determined ACL.
   *
   * @see \Google\Cloud\Storage\StreamWrapper::determineAclFromMode().
   */
  protected function determineAclFromMode($mode) {
    if ($mode & 0004) {
      // If any user can read, assume it should be publicRead.
      return 'publicRead';
    }
    elseif ($mode & 0040) {
      // If any group user can read, assume it should be projectPrivate.
      return 'projectPrivate';
    }

    // Otherwise, assume only the project/bucket owner can use the bucket.
    return 'private';
  }

  /**
   * Get directory info.
   *
   * In list objects calls, directories are returned with a trailing slash. By
   * providing the given path with a trailing slash as a list prefix, we can
   * check whether the given path exists as a directory. This is a copy of the
   * private function from Google's StreamWrapper class.
   *
   * @param string $path
   *   The directory to get.
   *
   * @return \Google\Cloud\Storage\StorageObject|NULL
   *   The directoy as a StorageObject or NULL on failure.
   *
   * @see \Google\Cloud\Storage\StreamWrapper::getDirectoryInfo().
   */
  protected function getDirectoryInfo($path) {
    if ($this->bucketManager->bucketExists()) {
      $scan = $this->bucketManager->getObjects(
        [
          'prefix' => $path . '/',
          'resultLimit' => 1,
          'fields' => 'items/name,items/size,items/updated,items/timeCreated,nextPageToken',
        ]
      );

      if (isset($scan)) {
        return $scan->current();
      }
    }

    return NULL;
  }

  /**
   * Make stat array.
   *
   * Returns the associative array that a `stat()` response expects using the
   * provided stats. Defaults the remaining fields to 0. This is a copy of the
   * private function from Google's StreamWrapper class.
   *
   * @param array $stats
   *   Sparse stats entries to set.
   *
   * @return array
   *   The populated stats array with defaults.
   *
   * @see \Google\Cloud\Storage\StreamWrapper::makeStatArray().
   */
  protected function makeStatArray($stats) {
    return array_merge(
      array_fill_keys(
        [
          'dev',
          'ino',
          'mode',
          'nlink',
          'uid',
          'gid',
          'rdev',
          'size',
          'atime',
          'mtime',
          'ctime',
          'blksize',
          'blocks'
        ],
        0
      ),
      $stats
    );
  }

  /**
   * Stats from file info.
   *
   * Given a `StorageObject` info array, extract the available fields into the
   * provided `$stats` array. This is a copy of the private function from
   * Google's StreamWrapper class.
   *
   * @param array $info
   *   Info array provided from a `StorageObject`.
   * @param array $stats
   *   Stats array to put the calculated stats into.
   *
   * @see \Google\Cloud\Storage\StreamWrapper::statsFromFileInfo().
   */
  protected function statsFromFileInfo(array &$info, array &$stats) {
    $stats['size'] = (isset($info['size'])) ? (int) $info['size'] : NULL;
    $stats['mtime'] = (isset($info['updated'])) ? strtotime($info['updated']) : NULL;
    $stats['ctime'] = (isset($info['timeCreated'])) ? strtotime($info['timeCreated']) : NULL;
  }

  /**
   * URL stat directory.
   *
   * Calculate the `url_stat` response for a directory. This is a copy of the
   * private function from Google's StreamWrapper class.
   *
   * @param \Google\Cloud\Storage\StorageObject $object
   *   The StorageObject object for the directory.
   *
   * @return array
   *   The stat array.
   *
   * @see \Google\Cloud\Storage\StreamWrapper::urlStatDirectory().
   */
  protected function urlStatDirectory(StorageObject $object) {
    $stats = [];
    if ($this->bucketManager->bucketExists()) {
      try {
        $info = $object->info();

        $stats['mode'] = $this->bucketManager->bucketWritable() ? self::DIRECTORY_WRITABLE_MODE : self::DIRECTORY_READABLE_MODE;
        $this->statsFromFileInfo($info, $stats);
      }
      catch (NotFoundException $e) {
      }
    }

    return $this->makeStatArray($stats);
  }

  /**
   * URL stat file.
   *
   * Calculate the `url_stat` response for a file
   *
   * @return array
   *   The stat array.
   *
   * @see \Google\Cloud\Storage\StreamWrapper::urlStatFile().
   */
  protected function urlStatFile() {
    if ($this->bucketManager->bucketExists()) {
      $object = $this->bucketManager->getObject(PathResolver::getTarget($this->uri));
      if (isset($object)) {
        $stats = [];
        try {
          $info = $object->info();

          $stats['mode'] = $this->bucketManager->bucketWritable() ? self::FILE_WRITABLE_MODE : self::FILE_READABLE_MODE;
          $this->statsFromFileInfo($info, $stats);

          return $this->makeStatArray($stats);
        }
        catch (ServiceException $e) {
        }
      }
    }

    return FALSE;
  }

}
