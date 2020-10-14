<?php

namespace Drupal\tide_media\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\File\Exception\FileNotExistsException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\file\Entity\File;
use Drupal\file\FileUsage\FileUsageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for deleting a media entity.
 */
abstract class FullFileDeletionForm extends ContentEntityConfirmFormBase {

  /**
   * The media being deleted.
   *
   * @var \Drupal\file\FileInterface
   */
  protected $entity;

  /**
   * The File Usage Service.
   *
   * @var \Drupal\file\FileUsage\FileUsageInterface
   */
  protected $fileUsage;

  /**
   * {@inheritdoc}
   */
  public function __construct(FileUsageInterface $file_usage, EntityRepositoryInterface $entity_repository, EntityTypeBundleInfoInterface $entity_type_bundle_info = NULL, TimeInterface $time = NULL) {
    $this->fileUsage = $file_usage;

    parent::__construct($entity_repository, $entity_type_bundle_info, $time);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('file.usage'), $container->get('entity.repository'), $container->get('entity_type.bundle.info'), $container->get('datetime.time'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $parent = parent::buildForm($form, $form_state);
    $file = $this->getCurrentFile();
    if (!$file) {
      return $parent;
    }
    $parent['info'] = [
      '#type' => 'table',
      '#header' => [
        'file name',
        'url',
        'delete',
      ],
    ];
    /** @var \Drupal\file\FileStorageInterface $file_storage */
    $file_storage = \Drupal::entityTypeManager()->getStorage('file');
    $key = preg_replace('/_[^_.]*\./', '.', $file->getFilename());
    $key = pathinfo($key, PATHINFO_FILENAME);
    $results = $file_storage->getQuery()
      ->condition('filename', '%' . $key . '%', 'LIKE')
      ->execute();
    if ($results) {
      $results = $file_storage->loadMultiple($results);
      $form_state->set('deleting_files', $results);
      /** @var \Drupal\file\FileInterface $result */
      foreach ($results as $id => $result) {
        $parent['info'][$id]['filename'] = [
          '#markup' => $result->getFilename(),
        ];
        $parent['info'][$id]['uri'] = [
          '#type' => 'link',
          '#title' => file_create_url($result->getFileUri()),
          '#attributes' => ['target' => '_blank'],
          '#url' => Url::fromUri(file_create_url($result->getFileUri())),
        ];
        $parent['info'][$id]['delete'] = [
          '#type' => 'link',
          '#title' => 'Delete',
          '#url' => Url::fromRoute('tide_media.file.delete_action', [
            'fid' => $result->id(),
            'base_entity_id' => $this->entity->getEntityTypeId() . '_' . $this->entity->id(),
          ]),
          '#access' => $result->id() !== $file->id() ?? FALSE,
          '#ajax' => [
            'dialogType' => 'modal',
            'progress' => [
              'type' => 'throbber',
              'message' => 'Loading...',
            ],
          ],
        ];
      }
    }
    return $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $files = $form_state->get('deleting_files');
    if ($files) {
      try {
        \Drupal::entityTypeManager()->getStorage('file')->delete($files);
      }
      catch (FileNotExistsException $exception) {
        watchdog_exception('tide_media', $exception);
      }
    }
    $this->entity->delete();
    parent::submitForm($form, $form_state);
  }

  /**
   * Try to get file entity.
   */
  private function getCurrentFile() {
    $entity_type_id = $this->entity->getEntityTypeId();
    switch ($entity_type_id) {
      case 'media':
        $fid = $this->entity->getSource()->getSourceFieldValue($this->entity);
        return File::load($fid);

      case 'file':
        return $this->entity;

      default:
        return NULL;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Full File Deletion');
  }

}
