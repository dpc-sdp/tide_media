<?php

namespace Drupal\tide_media\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\File\Exception\FileNotExistsException;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
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
    return new static(
      $container->get('file.usage'),
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'));
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $parent = parent::buildForm($form, $form_state);
    $file = $this->getCurrentFile();
    // If no file attached, just return.
    if (!$file) {
      return $parent;
    }
    // Basic settings.
    $parent['info'] = [
      '#type' => 'table',
      '#header' => [
        'to be deleting',
        'file name',
        'url',
        'delete',
        'media',
      ],
    ];
    /** @var \Drupal\file\FileStorageInterface $file_storage */
    $file_storage = \Drupal::entityTypeManager()->getStorage('file');
    // Try to get a search keyword.
    $key = preg_replace('/_[^_.]*\./', '.', $file->getFilename());
    $key = pathinfo($key, PATHINFO_FILENAME);
    // Searching from table.
    $results = $file_storage->getQuery()
      ->condition('filename', '%' . $key . '%', 'LIKE')
      ->execute();
    $parent['description']['#markup'] = t('Clicking the button will delete the file entirely from the system');
    if ($results) {
      $results = $file_storage->loadMultiple($results);
      /** @var \Drupal\file\FileInterface $result */
      foreach ($results as $id => $result) {
        $has_media = TRUE;
        $referenced = file_get_file_references($result, NULL, EntityStorageInterface::FIELD_LOAD_REVISION, '');
        if (empty($referenced)) {
          $has_media = FALSE;
        }
        $media = new \stdClass();
        if ($has_media) {
          if (isset($referenced['field_media_image'])) {
            foreach ($referenced['field_media_image']['media'] as $key => $entity) {
              $media = $entity;
            }
          }
          if (isset($referenced['field_media_file'])) {
            foreach ($referenced['field_media_file']['media'] as $key => $entity) {
              $media = $entity;
            }
          }
        }
        $parent['info'][$id]['to_be_deleting'] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Delete?'),
          '#default_value' => $result->id() == $file->id() ?? TRUE,
          '#disabled' => $result->id() == $file->id() ?? TRUE,
        ];
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
        $parent['info'][$id]['linked_media'] = [
          '#type' => 'inline_template',
          '#template' => '{% if has_media == false %}<p>{{ title }}</p>{% else %}<a href="{{ link }}" _target="_blank">{{ title }}</a>{% endif %}',
          '#context' => [
            'has_media' => $has_media,
            'title' => $has_media ? $media->getName() : 'No media linked',
            'link' => $has_media ? $media->toUrl()->toString() : NULL,
          ],
        ];
      }
    }
    return $parent;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $value = 0;
    $deleted_files = [];
    foreach (Element::children($form['info']) as $delta) {
      if ($form['info'][$delta]['to_be_deleting']['#value'] == 1) {
        $deleted_files[] = $delta;
      }
      $value += $form['info'][$delta]['to_be_deleting']['#value'];
    }
    if ($value == 0) {
      $form_state->setError($form['info'], 'You must select at least 1 file to delete.');
    }
    $form_state->set('deleted_files', $deleted_files);
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $file_ids = $form_state->get('deleted_files');
    if ($file_ids) {
      $files = File::loadMultiple($file_ids);
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
