<?php

namespace Drupal\tide_media;

/**
 * Helper class for install/update ops.
 */
class TideOperation {

  /**
   * Enables standalone media URL for video transcripts.
   */
  public static function enableStandaloneMedia() {
    $config_factory = \Drupal::configFactory();
    $config = $config_factory->getEditable('media.settings');
    $config->set('standalone_url', TRUE);
    $config->save();
  }

}
