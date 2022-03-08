<?php

namespace Drupal\tide_media\Plugin\Validation\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * Validation constraint for the video embed field.
 *
 * @Constraint(
 *   id = "VideoEmbedValidation",
 *   label = @Translation("VideoEmbed provider constraint", context = "Validation"),
 * )
 */
class MediaVideoConstraint extends Constraint {

  /**
   * Message shown when a video provider is not found.
   *
   * @var string
   */
  public $message = 'Could not find a video provider to handle the given URL.';

}
