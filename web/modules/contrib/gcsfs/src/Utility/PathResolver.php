<?php

namespace Drupal\gcsfs\Utility;

use Drupal\Core\StreamWrapper\StreamWrapperManager;

/**
 * Path Resolver Utility class.
 */
final class PathResolver {

  /**
   * Get Scheme.
   *
   * Wrapper for the StreamWrapper::getScheme() method but with static caching
   * for performance.
   *
   * @param string $uri
   *   The URI to get the scheme for.
   *
   * @return string
   *   The scheme of the URI.
   */
  public static function getScheme(string $uri): string {
    $schemes = &drupal_static(__METHOD__);

    if (!isset($schemes[$uri])) {
      if (!isset($schemes)) {
        $schemes = [];
      }
      
      $schemes[$uri] = StreamWrapperManager::getScheme($uri);
    }

    return $schemes[$uri];
  }

  /**
   * Get Target.
   *
   * Wrapper for the StreamWrapper::getTraget() method but with static caching
   * for performance.
   *
   * @param string $uri
   *   The URI to get the target for.
   *
   * @return string
   *   The target of the URI.
   */
  public static function getTarget(string $uri): string {
    $targets = &drupal_static(__METHOD__);

    if (!isset($targets[$uri])) {
      if (!isset($targets)) {
        $targets = [];
      }
      $targets[$uri] = StreamWrapperManager::getTarget($uri);
    }

    return $targets[$uri];
  }

  /**
   * Resolve.
   *
   * Resolves references to /./, /../ and extra / characters in the input URI
   * and returns the canonicalized URI.
   *
   * @param string $uri
   *   The URI to process.
   *
   * @return string
   *   The resolved URI.
   */
  public static function resolve(string $uri): string {
    $resolved = &drupal_static(__METHOD__);

    if (!isset($resolved[$uri])) {
      if (!isset($resolved)) {
        $resolved = [];
      }

      $scheme = self::getScheme($uri);
      $path = self::getTarget($uri);
      $path_parts = explode('/', $path);

      // Re-build the path.
      $new_path = [];
      foreach ($path_parts as $part) {
        if ($part != '.' && mb_strlen($part) > 0) {
          if ($part != '..') {
            $new_path[] = $part;
          }
          elseif (count($new_path) > 0) {
            array_pop($new_path);
          }
        }
      }

      $resolved[$uri] = $scheme . '://' . implode('/', $new_path);
    }

    return $resolved[$uri];
  }

}
