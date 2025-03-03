<?php

/**
 * @file
 * Batch functions for the Google Cloud Storage File System module.
 */

use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Refresh Metadata Batch: Get Metadata.
 *
 * @param mixed $context
 *   The batch context.
 */
function _gcsfs_metadata_refresh_batch_get_metadata(&$context) {
  $context['results']['objects'] = [];
  $context['message'] = new TranslatableMarkup('Fetching objects from Google Cloud Storage bucket.');

  $objects = \Drupal::service('gcsfs.bucket_manager')->getObjects();
  foreach ($objects as $object) {
    $info = $object->info();
    $context['results']['objects'][$info['name']] = [
      'path' => rtrim($info['name'], '/'),
      'file_size' => $info['size'],
      'directory' => (int) ($info['name'] != rtrim($info['name'], '/')),
      'created' => strtotime($info['timeCreated']),
    ];
  }
}

/**
 * Refresh Metadata Batch: Update Metadata.
 *
 * @param mixed $context
 *   The batch context.
 */
function _gcsfs_metadata_refresh_batch_update_metadata(&$context) {
  if (count($context['results']['objects'])) {
  
    // Handle the sandbox.
    if (!isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress'] = 0;
      $context['sandbox']['max'] = count($context['results']['objects']);
    }

    $paths = array_keys($context['results']['objects']);
    $object = $context['results']['objects'][$paths[$context['sandbox']['progress']]];
    $context['message'] = new TranslatableMarkup('Creating/Updating metadata for %path.', ['%path' => $object['path']]);
    \Drupal::database()->upsert('gcsfs_object_metadata')
      ->fields(array_keys($object))
      ->key('path')
      ->values($object)
      ->execute();
    $context['sandbox']['progress']++;

    // Check for finished state, set the percentage done if not finished.
    if ($context['sandbox']['progress'] != $context['sandbox']['max']) {
      $context['finished'] = $context['sandbox']['progress'] / $context['sandbox']['max'];
    }
  }
}

/**
 * Refresh Metadata Batch: Finished.
 *
 * @param bool $success
 *   Whether the batch completed successfully or not.
 * @param array $results
 *   The results of the batch.
 * @param array $operations
 *   The remaining operations if the batch was unsuccessful.
 * @param string $elapsed
 *   The time it took to run the batch.
 */
function _gcsfs_metadata_refresh_batch_finished(bool $success, array $results, array $operations, string $elapsed) {
  if ($success) {
    \Drupal::messenger()->addMessage(
      \Drupal::translation()->formatPlural(
        count($results['objects']),
        'Metadata refreshed for @count object.',
        'Metadata refreshed for @count objects.',
        [
          '@count' => count($results['objects']),
        ]
      )
    );
  }
}
