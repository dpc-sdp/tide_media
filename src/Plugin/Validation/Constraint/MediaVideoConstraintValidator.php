<?php

namespace Drupal\tide_media\Plugin\Validation\Constraint;

use Drupal\video_embed_field\Plugin\Validation\Constraint\VideoEmbedConstraintValidator;
use Symfony\Component\Validator\Constraint;

/**
 * Validates the video embed providers.
 */
class MediaVideoConstraintValidator extends VideoEmbedConstraintValidator {

  /**
   * {@inheritdoc}
   */
  public function validate($field, Constraint $constraint) {
    if (!isset($field->value)) {
      return NULL;
    }

    $allowed_providers = $field->getFieldDefinition()->getSetting('allowed_providers');
    $allowed_provider_definitions = $this->providerManager->loadDefinitionsFromOptionList($allowed_providers);

    if (FALSE === $this->providerManager->filterApplicableDefinitions($allowed_provider_definitions, $field->value)) {
      $this->context->addViolation($constraint->message);
    }
  }

}
