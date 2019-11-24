@tide
# @TODO remove @skipped once the module is extracted to its own repo.
Feature: Configuration for File Upload Overwrite

  Ensure that the configuration page has the expected fields.

  @api @javascript
  Scenario: The the configuration page has the expected fields.
    Given I am logged in as a user with the "administrator" role
    # tide_media_file_overwrite moudle doesn't have dependency to tide_media
    # so we enable this module directly from admin/modules for testing purpose.
    When I visit "admin/modules"
    And save screenshot
    And I check "Tide Media File Overwrite"
    And save screenshot
    And I press "Install"
    And save screenshot
    Then I should see the success message "Module Tide Media File Overwrite has been enabled."
    When I visit "admin/config/tide_media_file_overwrite/settings"
    And save screenshot
    Then I see field "Overwrite upload file if the same file name exists?"
    And I should see an "input#edit-needs-overwritten" element
    And I see field "Media form field map"
    And I should see an "textarea#edit-media-form-field-map" element