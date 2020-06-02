/**
 * @file
 * Media form.
 */

(function ($, Drupal, drupalSettings) {
  'use strict';

  Drupal.behaviors.tide_media_media_form = {
    attach: function (context, settings) {
      if(drupalSettings.form_media.update_name_on_change) {
        $('.form-file').change(function (event) {
          var files = event.currentTarget.files;

          if (files.length > 0) {
            $('#edit-name-wrapper input').val(files[0].name);
          }
        });
      }
    }
  };
}(jQuery, Drupal, drupalSettings));
