@customfield @customfield_radio @javascript
Feature: Managers can manage course custom fields radio
  In order to have additional data on the course
  As a manager
  I need to create, edit, remove and sort custom fields

  Background:
    Given the following "custom field categories" exist:
      | name              | component   | area   | itemid |
      | Category for test | core_course | course | 0      |
    And I log in as "admin"
    And I navigate to "Courses > Default settings > Course custom fields" in site administration

  Scenario: Create a custom course radio field
    When I click on "Add a new custom field" "link"
    And I click on "Radio" "link"
    And I set the following fields to these values:
      | Name       | Test field |
      | Short name | testfield  |
    And I set the field "Radio options (one per line)" to multiline:
    """
    a
    b
    """
    And I click on "Save changes" "button" in the "Adding a new Radio" "dialogue"
    Then I should see "Test field"
    And I log out

  Scenario: Edit a custom course radio field
    When I click on "Add a new custom field" "link"
    And I click on "Radio" "link"
    And I set the following fields to these values:
      | Name       | Test field |
      | Short name | testfield  |
    And I set the field "Radio options (one per line)" to multiline:
    """
    a
    b
    """
    And I click on "Save changes" "button" in the "Adding a new Radio" "dialogue"
    And I click on "Edit" "link" in the "Test field" "table_row"
    And I set the following fields to these values:
      | Name | Edited field |
    And I click on "Save changes" "button" in the "Updating Test field" "dialogue"
    Then I should see "Edited field"
    And I should not see "Test field"
    And I log out

  Scenario: Delete a custom course radio field
    When I click on "Add a new custom field" "link"
    And I click on "Radio" "link"
    And I set the following fields to these values:
      | Name       | Test field |
      | Short name | testfield  |
    And I set the field "Radio options (one per line)" to multiline:
    """
    a
    b
    """
    And I click on "Save changes" "button" in the "Adding a new Radio" "dialogue"
    And I click on "Delete" "link" in the "Test field" "table_row"
    And I click on "Yes" "button" in the "Confirm" "dialogue"
    Then I should not see "Test field"
    And I log out

  Scenario: Validation of custom course radio field configuration
    When I click on "Add a new custom field" "link"
    And I click on "Radio" "link"
    And I set the following fields to these values:
      | Name       | Test field |
      | Short name | testfield  |
    And I click on "Save changes" "button" in the "Adding a new Radio" "dialogue"
    And I should see "Please provide at least two options, with each on a new line." in the "Radio options (one per line)" "form_row"
    And I set the field "Radio options (one per line)" to multiline:
    """
    a
    b
    """
    And I set the field "Default value" to "c"
    And I click on "Save changes" "button" in the "Adding a new Radio" "dialogue"
    And I should see "The default value must be one of the options from the list above" in the "Default value" "form_row"
    And I set the field "Default value" to "b"
    And I click on "Save changes" "button" in the "Adding a new Radio" "dialogue"
    And "testfield" "text" should exist in the "Test field" "table_row"
    And I log out
