<?php

namespace Drupal\tide_media;

/**
 * Helper class for install/update ops.
 */
class TideOperation {

  /**
   * Helper to install a module.
   *
   * @param string $module
   *   Module name.
   *
   * @throws \Exception
   *   When module already installed.
   */
  public function tideMediaInstallModule($module) {
    /** @var \Drupal\Core\Extension\ModuleHandler $moduleHandler */
    $moduleExists = \Drupal::service('module_handler')->moduleExists($module);
    // Check if module is both installed and enabled.
    if (!$moduleExists) {
      // If not, install the queue_mail module.
      \Drupal::service('module_installer')->install([$module]);
    }
  }

  /**
   * Enables standalone media URL for video transcripts.
   */
  public static function enableStandaloneMedia() {
    $config_factory = \Drupal::configFactory();
    $config = $config_factory->getEditable('media.settings');
    $config->set('standalone_url', TRUE);
    $config->save();
  }

  /**
   * Enables entity_usage module.
   */
  public static function enableEntityUsage() {
    $this->tideMediaInstallModule('entity_usage');
  }

}
