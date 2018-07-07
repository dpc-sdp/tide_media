@tide
Feature: Media browser

  As a privileged user, I want to use media browser.

  @api @javascript
  Scenario: Media Browser is available.
    Given I am logged in as a user with the "create test content, access media overview, access tide_media_browser entity browser pages, access tide_media_browser_iframe entity browser pages, use text format rich_text" permission
    When I visit "/node/add/test"
    Then I should see the text "Create Test"

    Then I should see a "#cke_edit-body-0-value .cke_top .cke_button__tide_media" element
    And I click "Media"
    And I wait for AJAX to finish
    And I wait for 5 seconds
    Then I should see the text "Select media to embed"
    And I press the "Close" button

    And I should see the text "Media"
    And I press the "Select media" button
    And I wait for AJAX to finish
    Then I should see the text "Select media"
