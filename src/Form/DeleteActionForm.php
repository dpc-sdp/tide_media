<?php

namespace Drupal\tide_media\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;

/**
 * Delete action form.
 */
class DeleteActionForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tide_file_delete_action_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, int $fid = NULL, string $base_entity_id = NULL, int $media_id = NULL) {
    $file = File::load($fid);
    $media = Media::load($media_id);
    $form['info'] = [
      '#markup' => t('Are you sure you want to delete the media %title ?', [
        '%title' => $file->getFilename(),
      ]),
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => 'Delete',
      '#button_type' => 'primary',
    ];
    [$entity_type_id, $id] = explode('_', $base_entity_id);
    $form_state->set('file', $file);
    $form_state->set('redirect_route', 'entity.' . $entity_type_id . '.delete_form');
    $form_state->set('redirect_route_entity_type_id', $entity_type_id);
    $form_state->set('redirect_entity_id', $id);
    $form_state->set('media', $media);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    try {
      \Drupal::entityTypeManager()
        ->getStorage('file')
        ->delete([$form_state->get('file')]);
      \Drupal::entityTypeManager()
        ->getStorage('media')
        ->delete([$form_state->get('media')]);
    }
    catch (\Exception $exception) {
      watchdog_exception('tide_media', $exception);
    }
    try {
      $form_state->setRedirect($form_state->get('redirect_route'), [$form_state->get('redirect_route_entity_type_id') => $form_state->get('redirect_entity_id')]);
    }
    catch (\Exception $exception) {
      watchdog_exception('tide_media', $exception);
    }
  }

}
