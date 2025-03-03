<?php

namespace Drupal\gcsfs\Commands;

use Drupal\gcsfs\BucketManagerInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drush\Commands\DrushCommands;

/**
 * Refresh metadata.
 */
class Refresh extends DrushCommands {

  /**
   * Bucket manager service.
   *
   * @var \Drupal\gcsfs\BucketManagerInterface
   */
  protected BucketManagerInterface $bucketManager;

  /**
   * Extension list service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected ModuleExtensionList $extensionList;

  /**
   * Constructor.
   *
   * @param \Drupal\gcsfs\BucketManagerInterface $bucket_manager
   *   The bucket manager service.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list
   *   The extension list service.
   */
  public function __construct(BucketManagerInterface $bucket_manager, ModuleExtensionList $extension_list) {
    $this->bucketManager = $bucket_manager;
    $this->extensionList = $extension_list;
  }

  /**
   * Refresh metadata from Google Cloud Storage bucket.
   *
   * @command gcsfs-refresh
   * @usage Standard example
   *   drush gcsfs-refresh
   * @aliases gcsfsr
   */
  public function refreshMetadata() {
    if ($this->bucketManager->bucketExists()) {

      // Set up the batch.
      $batch = new BatchBuilder();
      $batch->setTitle(new TranslatableMarkup('Refreshing metadata from Google Cloud Storage bucket.'));
      $batch->setFile($this->extensionList->getPath('gcsfs') . '/gcsfs.batch.inc');
      $batch->setFinishCallback('_gcsfs_metadata_refresh_batch_finished');
      $batch->addOperation('_gcsfs_metadata_refresh_batch_get_metadata', []);
      $batch->addOperation('_gcsfs_metadata_refresh_batch_update_metadata', []);
      batch_set($batch->toArray());

      drush_backend_batch_process();

      $this->logger()->notice(dt('Metadata refresh completed successfully.'));
    }
  }

}
