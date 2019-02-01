@tide
Feature: Media view filter addition

  As a site admin, I want to be able to filter media based on License.

  @api @javascript
  Scenario: Media view has Site filter
    Given I am logged in as an administrator
    When I visit "admin/content/media"
    And I should see "License" in the "label[for=edit-field-media-license-value]" element
