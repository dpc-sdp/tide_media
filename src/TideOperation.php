<?php

namespace Drupal\tide_media;

use Drupal\taxonomy\Entity\Term;
use Drupal\user\Entity\Role;

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
  public static function tideMediaInstallModule($module) {
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
   * Set default settings for entity_usage module.
   */
  public static function entityUsageDefaultSettings() {
    if (\Drupal::moduleHandler()->moduleExists('entity_usage')) {
      $config_factory = \Drupal::configFactory();
      $config = $config_factory->getEditable('entity_usage.settings');
      $enabled_plugins = [
        'block_field',
        'dynamic_entity_reference',
        'entity_embed',
        'entity_reference',
        'html_link',
        'layout_builder',
        'link',
        'linkit',
        'media_embed',
      ];
      $entity_types = [
        'media',
      ];
      // Set where it will check for media items use.
      $source = [
        'block',
        'node',
        'block_content',
        'paragraph',
        'taxonomy_term',
      ];
      // Set default setting only to track media items.
      $config->set('local_task_enabled_entity_types', $entity_types);
      $config->set('track_enabled_source_entity_types', $source);
      $config->set('track_enabled_target_entity_types', $entity_types);
      $config->set('track_enabled_plugins', $enabled_plugins);
      $config->save();

      // Add required permissions.
      $roles = ['approver', 'site_admin', 'editor'];
      $permissions = [
        'access entity usage statistics',
      ];
      foreach ($roles as $role) {
        user_role_grant_permissions(Role::load($role)->id(), $permissions);
      }
    }
  }

  /**
   * Assign necessary permissions .
   */
  public static function assignNecessaryPermissions() {
    $site_admin_contributor = [
      'site_admin',
      'approver',
      'editor',
      'contributor',
    ];

    $permissions = [
      'access tide_document_browser entity browser pages' => $site_admin_contributor,
      'access tide_image_browser entity browser pages' => $site_admin_contributor,
      'access tide_media_browser entity browser pages' => $site_admin_contributor,
      'access tide_media_browser_iframe entity browser pages' => $site_admin_contributor,
    ];

    /** @var \Drupal\user\RoleInterface[] $roles */
    $roles = \Drupal::entityTypeManager()->getStorage('user_role')->loadMultiple();

    foreach ($roles as $rid => $role) {
      foreach ($permissions as $permission => $permission_rids) {
        if (in_array($rid, $permission_rids)) {
          $role->grantPermission($permission);
        }
      }
      $role->save();
    }
  }

  /**
   * Creates terms for license_type vocabulary.
   */
  public static function createLicenseTypeTerms() {
    $terms = [
      'Copyright',
      'Creative Commons Attribution 4.0',
    ];
    foreach ($terms as $term) {
      $result = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadByProperties([
          'name' => $term,
          'vid'  => 'license_type',
        ]);
      if (empty($result)) {
        Term::create([
          'name' => $term,
          'vid' => 'license_type',
          'parent' => [],
        ])->save();
      }
    }
  }

}
