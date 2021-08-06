<?php

class TideOperation {
  public static function enable_standalone_media() {
    // Enables standalone media URL for video transcripts.
    $config_factory = \Drupal::configFactory();
    $config = $config_factory->getEditable('media.settings');
    $config->set('standalone_url', TRUE);
    $config->save();
  }
}
