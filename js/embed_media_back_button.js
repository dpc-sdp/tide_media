/**
 * @file
 * Adds the back button for embeded media select modal in CKEditor.
 * Everytime the user goes to different page within the media embed select modal
 * it will save the filter values to retrive the search result when user
 * comes back to the first page.
 */

 (function ($, Drupal) {
  'use strict';

  Drupal.behaviors.tide_media_browser_iframe = {
    attach: function (context, settings) {
      if (!window.CKEDITOR) {
        return;
      }
      let iframePath, itemName, licenseType, mediaType, published, site
      const iframeName = 'entity_browser_iframe_tide_media_browser_iframe'
      const homePagePath = '/entity-browser/iframe/tide_media_browser_iframe'
      const backButton = $('#entity-embed-dialog-tide-media-browser-home')
      $(document).ready(function () {
        // Remove the name field value on modal first load if any.
        localStorage.removeItem("tideMediaBrowsernameFilterVal");
        $("iframe").on("load", function() {
          if ($("iframe") && $("iframe").length > 0) {
            for (let i = 0; i < $("iframe").length; i++) {
              if ($("iframe")[i].name && $("iframe")[i].name === iframeName) {
                iframePath = $("iframe")[i].contentWindow.location.pathname
                // Shows the back button when iframe is not in the first page.
                if (iframePath && iframePath !== homePagePath) {
                  backButton.css("display", "block");
                }
                // Hides the back button for the first page.
                if (iframePath && iframePath === homePagePath) {
                  backButton.css("display", "none");
                }
              }
            }
          }
        });
      });
      // Disable back button when it is on the first page.
      backButton.click(function() {
        iframePath = $("iframe")[1].contentWindow.location.pathname
        if (iframePath && iframePath !== homePagePath) {
          $("iframe")[1].contentWindow.history.back()
        }
        $("iframe").on("load", function() {
          if ($("iframe") && $("iframe").length > 0) {
            for (let i = 0; i < $("iframe").length; i++) {
              if ($("iframe")[i].name && $("iframe")[i].name === iframeName) {
                // Get the filter values on iframe load.
                itemName =  localStorage.getItem("tideMediaBrowsernameFilterVal")
                $(this).contents().find('#edit-name').val(itemName)
                licenseType = $(this).contents().find('#edit-field-license-type-target-id-1 :selected').text()
                mediaType = $(this).contents().find('#edit-bundle :selected').text()
                published = $(this).contents().find('#edit-status :selected').text()
                site = $(this).contents().find('#edit-field-media-site-target-id :selected').text()
                // Trigger ajax call submit to retrive the user search for current session.
                // Will only trigger if there is any filter value set for this session.
                if (checkFilterValuesOnLoad (itemName, licenseType, mediaType, published, site)) {
                  $(this).contents().find('#edit-submit-tide-media-browser').trigger('click');
                }
              }
            }
          }
        })
      });
      // Check filter values on load.
      function checkFilterValuesOnLoad (itemName, licenseType, mediaType, published, site) {
        const defaultValue = "- Any -"
        if ((itemName && itemName !== 'undefined') ||
          (licenseType && licenseType !== defaultValue) || 
          (mediaType && mediaType !== defaultValue) || 
          (published && published !== defaultValue) || 
          (site && site !== defaultValue)) {
            return true
        }
        return false
      }
    },
  };
}(jQuery, Drupal));