/**
 * @file
 * Retrieve the name user input for embed media select iframe.
 * Everytime the user goes to different page within the media embed select modal
 * it will save the name field value to retrive the search result when user
 * comes back to the first page.
 */

(function ($, Drupal) {
    'use strict';

    Drupal.behaviors.tide_media_media_form = {
      attach: function (context, settings) {
        let nameField = $('#views-exposed-form-tide-media-browser-media-browser input[name=name]')
        const fromSubmit = $('#views-exposed-form-tide-media-browser-media-browser input[type=submit]')
        $(document).ready(function () {
          // Retrieving value from session and setting it to the name field.
          nameField.val(localStorage.getItem("tideMediaBrowsernameFilterVal"))
        })
        // On every submit it will save the namefield value to the localstorage.
        fromSubmit.click(function () {
          if (nameField.val() && nameField.val() !== "undefined") {
            localStorage.setItem("tideMediaBrowsernameFilterVal", nameField.val());
          }
        })
      }
    };
}(jQuery, Drupal));
