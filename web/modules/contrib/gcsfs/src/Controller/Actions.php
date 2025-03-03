<?php

namespace Drupal\gcsfs\Controller;

use Drupal\gcsfs\BucketManagerInterface;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Actions class.
 */
class Actions extends ControllerBase {

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
   * Time service.
   *
   * @var \Drupal\Component\Datetime\TimeInterface
   */
  protected TimeInterface $time;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->bucketManager = $container->get('gcsfs.bucket_manager');
    $instance->time = $container->get('datetime.time');
    $instance->extensionList = $container->get('extension.list.module');

    return $instance;
  }

  public function refresh() {
    if ($this->bucketManager->bucketExists()) {

      // Set up the batch.
      $batch = new BatchBuilder();
      $batch->setTitle($this->t('Refreshing metadata from Google Cloud Storage bucket.'));
      $batch->setFile($this->extensionList->getPath('gcsfs') . '/gcsfs.batch.inc');
      $batch->setFinishCallback('_gcsfs_metadata_refresh_batch_finished');
      $batch->addOperation('_gcsfs_metadata_refresh_batch_get_metadata', []);
      $batch->addOperation('_gcsfs_metadata_refresh_batch_update_metadata', []);
      batch_set($batch->toArray());

      return batch_process(Url::fromRoute('gcsfs.config'));
    }

    return $this->redirect('gcsfs.config');
  }

  /**
   * Test connection.
   *
   * Test the connection to Google cloud storage using the configured bucket
   * and credentials file.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect response.
   */
  public function test() {
    if ($this->bucketManager->bucketExists()) {
      $this->messenger()->addMessage($this->t('Bucket exists.'));

      $current_time = $this->time->getCurrentTime();
      $directory = 'gs://drupal_test-' . $current_time . '/sub-folder';
      if (mkdir($directory)) {
        $this->messenger()->addMessage($this->t('Directory (%directory) created.', ['%directory' => $directory]));

        $file1 = $directory . '/file1.txt';
        $file_contents = 'This is the contents of the file.';
        if ($fh = fopen($file1, 'w')) {
          fwrite($fh, $file_contents);
          fclose($fh);
          $this->messenger()->addMessage($this->t('File (%file) created.', ['%file' => $file1]));
        }

        $file2 = $directory . '/file2.txt';
        if (file_put_contents($file2, $file_contents)) {
          $this->messenger()->addMessage($this->t('File (%file) created.', ['%file' => $file2]));
        }

        $file3 = $directory . '/file3.txt';
        if (rename($file2, $file3)) {
          $this->messenger()->addMessage($this->t('File (%file1) renamed (%file2).', ['%file1' => $file2, '%file2' => $file3]));
          $file2 = $file3;
        }

        if ($dh = opendir($directory)) {
          while (($file = readdir($dh)) !== FALSE) {
            print $file . "\n";
          }
          rewinddir($dh);
          while (($file = readdir($dh)) !== FALSE) {
            print $file . "\n";
          }
          closedir($dh);

          $files = scandir($directory);
          foreach ($files as $file) {
            print $file . "\n";
          }
        }

        $new_directory = str_replace('sub-folder', 'another', $directory);
        if (rename($directory, $new_directory)) {
          $this->messenger()->addMessage($this->t('Directory (%directory) renamed to (%new_directory).', ['%directory' => $new_directory, '%new_directory' => $new_directory]));
          $directory = $new_directory;
          $file1 = str_replace('sub-folder', 'another', $file1);
          $file2 = str_replace('sub-folder', 'another', $file2);
        }

        if (unlink($file1)) {
          $this->messenger()->addMessage($this->t('File (%file) removed.', ['%file' => $file1]));
        }

        if (unlink($file2)) {
          $this->messenger()->addMessage($this->t('File (%file) removed.', ['%file' => $file2]));
        }

        if (rmdir($directory)) {
          $this->messenger()->addMessage($this->t('Directory (%directory) removed.', ['%directory' => $directory]));
          $directory = dirname($directory);
          if (rmdir($directory)) {
            $this->messenger()->addMessage($this->t('Directory (%directory) removed.', ['%directory' => $directory]));
          }
        }
      }
    }

    return $this->redirect('gcsfs.config');
  }

}
