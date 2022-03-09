<?php

namespace Drupal\tide_media\Plugin\Validation\Constraint;

use Drupal\video_embed_field\Plugin\Validation\Constraint\VideoEmbedConstraint;

/**
 * Validation constraint for the video embed field.
 *
 * @Constraint(
 *   id = "tide_media_video_embed_validation",
 *   label = @Translation("VideoEmbed provider constraint", context = "Validation"),
 * )
 */
class MediaVideoConstraint extends VideoEmbedConstraint {

  /**
   * Message shown when a video provider is not found.
   *
   * @var string
   */
  public $message = 'Could not find a video provider to handle the given URL.';

}
