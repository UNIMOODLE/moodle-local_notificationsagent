@managetemplates @notificationsagent @javascript
Feature: Testing templates management in notifications agent plugin
  In order to test the template management
  As an admin
  I should be able to configure and manage templates in the notifications agent plugin

  Background:
    Given the following "course" exists:
      | fullname  | Test notificationsagent |
      | shortname | testnotifagent |
      | format    | topics|
    And the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | testnotifagent | student |
    And I log in as "admin"
    And I wait "2" seconds

  Scenario: Create template for plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I click on "New template" "link"
    And I set the following fields to these values:
      | Title | Testing template1 |
    And I select "[TTTT] has passed since the user last session in the course." from the "id_newcondition_select" singleselect
    And I click on "newcondition_button" "button"
    And I click on the input element with placeholder "Days" inside div with id "nav-conditions"
    And I press the left key
    And I press the delete key
    And I type "3"
    And I click on "Actions" "link"
    And I select "Send notification to user [UUUU] with title [TTTT] and message [BBBB]" from the "id_newaction_select" singleselect
    And I click on "newaction_button" "button"
    And I set the following fields to these values:
      | Title [TTTT]:  | New notification |
      | Message [BBBB] | Hi {User_Username}, your last session in the course was 3 days ago. |
    When I click on "Save changes" "button"
    And I wait "1" seconds
    Then I should see "saved"
    And I should see "Testing template1"
    And I should see "template"
    And I wait "2" seconds
  
  Scenario: Edit template for plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I click on "New template" "link"
    And I set the following fields to these values:
      | Title | Testing template1 |
    And I select "[TTTT] has passed since the user last session in the course." from the "id_newcondition_select" singleselect
    And I click on "newcondition_button" "button"
    And I click on the input element with placeholder "Days" inside div with id "nav-conditions"
    And I press the left key
    And I press the delete key
    And I type "3"
    And I click on "Actions" "link"
    And I select "Send notification to user [UUUU] with title [TTTT] and message [BBBB]" from the "id_newaction_select" singleselect
    And I click on "newaction_button" "button"
    And I set the following fields to these values:
      | Title [TTTT]:  | New notification |
      | Message [BBBB] | Hi {User_Username}, example text. |
    And I click on "Save changes" "button"
    And I wait "1" seconds
    And I should see "saved"
    And I should see "Testing template1"
    And I should see "template"
    And I wait "2" seconds
    When I click on "Edit" "link"
    And I set the following fields to these values:
      | Title | template1 EDITED NAME |
      | Message [BBBB] | Hi {User_Username}, EDITED message. |
    And I click on the input element with placeholder "Days" inside div with id "nav-conditions"
    And I type "0"
    And I click on "Save changes" "button"
    And I should see "saved"
    Then I should see "template1 EDITED NAME"
    And I should see "30 days has passed since the user last session in the course."
    And I wait "2" seconds

  Scenario: Delete template for plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I click on "New template" "link"
    And I set the following fields to these values:
      | Title | Testing template1 |
    And I select "[TTTT] has passed since the user last session in the course." from the "id_newcondition_select" singleselect
    And I click on "newcondition_button" "button"
    And I click on the input element with placeholder "Days" inside div with id "nav-conditions"
    And I press the left key
    And I press the delete key
    And I type "3"
    And I click on "Actions" "link"
    And I select "Send notification to user [UUUU] with title [TTTT] and message [BBBB]" from the "id_newaction_select" singleselect
    And I click on "newaction_button" "button"
    And I set the following fields to these values:
      | Title [TTTT]:  | New notification |
      | Message [BBBB] | Hi {User_Username}, your last session in the course was 3 days ago. |
    And I click on "Save changes" "button"
    And I wait "1" seconds
    And I should see "saved"
    And I should see "Testing template1"
    And I should see "template"
    And I wait "2" seconds
    When I click on "Delete" "link"
    And I wait "1" seconds
    And I press tab
    And I press tab
    And I press tab
    And I press the enter key
    And I wait "1" seconds
    Then I should see "deleted"
    And I should not see "Testing template1"
    And I wait "2" seconds

  Scenario: Assign template for plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I click on "New template" "link"
    And I set the following fields to these values:
      | Title | Testing template1 |
    And I select "[TTTT] has passed since the user last session in the course." from the "id_newcondition_select" singleselect
    And I click on "newcondition_button" "button"
    And I click on "Actions" "link"
    And I select "Send notification to user [UUUU] with title [TTTT] and message [BBBB]" from the "id_newaction_select" singleselect
    And I click on "newaction_button" "button"
    And I set the following fields to these values:
      | Title [TTTT]:  | New notification |
      | Message [BBBB] | Hi {User_Username}, your last session in the course was 3 days ago. |
    And I click on "Save changes" "button"
    And I wait "1" seconds
    And I should see "saved"
    And I should see "Testing template1"
    And I should see "template"
    And I wait "2" seconds
    When I click on "Select" "link"
    And I click on "checkboxcategory-1" "checkbox"
    And I click on "saveassignTemplateModal" "button"
    And I wait "2" seconds
    And I am on "testnotifagent" course homepage
    And I click on "More" if it exists otherwise "My assistant"
    And I wait "1" seconds
    And I click on "Add rule" "link"
    And I wait "1" seconds
    And I click on "Create from this template" "link"
    And I set the following fields to these values:
      | Title | template1 ASSIGNED |
    And I click on the input element with placeholder "Days" inside div with id "nav-conditions"
    And I press the left key
    And I press the delete key
    And I type "3"
    And I click on "Save changes" "button"
    And I should see "saved"
    And I should see "Active"
    Then I should see "template1 ASSIGNED"
    And I wait "2" seconds

  Scenario: Export template for plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I click on "New template" "link"
    And I set the following fields to these values:
      | Title | Testing template1 |
    And I select "[TTTT] has passed since the user last session in the course." from the "id_newcondition_select" singleselect
    And I click on "newcondition_button" "button"
    And I click on the input element with placeholder "Days" inside div with id "nav-conditions"
    And I press the left key
    And I press the delete key
    And I type "3"
    And I click on "Actions" "link"
    And I select "Send notification to user [UUUU] with title [TTTT] and message [BBBB]" from the "id_newaction_select" singleselect
    And I click on "newaction_button" "button"
    And I set the following fields to these values:
      | Title [TTTT]:  | New notification |
      | Message [BBBB] | Hi {User_Username}, your last session in the course was 3 days ago. |
    When I click on "Save changes" "button"
    And I wait "1" seconds
    And I should see "saved"
    And I should see "Testing template1"
    And I should see "template"
    Then I click on "Export" "link"
    And I wait "2" seconds

  Scenario: Delete template with assigned courses
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I click on "New template" "link"
    And I set the following fields to these values:
      | Title | Testing template1 |
    And I select "[TTTT] has passed since the user last session in the course." from the "id_newcondition_select" singleselect
    And I click on "newcondition_button" "button"
    And I click on the input element with placeholder "Days" inside div with id "nav-conditions"
    And I press the left key
    And I press the delete key
    And I type "3"
    And I click on "Actions" "link"
    And I select "Send notification to user [UUUU] with title [TTTT] and message [BBBB]" from the "id_newaction_select" singleselect
    And I click on "newaction_button" "button"
    And I set the following fields to these values:
      | Title [TTTT]:  | New notification |
      | Message [BBBB] | Hi {User_Username}, your last session in the course was 3 days ago. |
    And I click on "Save changes" "button"
    And I wait "1" seconds
    And I should see "saved"
    And I should see "Testing template1"
    And I should see "template"
    And I wait "2" seconds
    And I click on "Select" "link"
    And I click on "checkboxcategory-1" "checkbox"
    And I click on "saveassignTemplateModal" "button"
    And I wait "1" seconds
    When I click on "Delete" "link"
    And I wait "1" seconds
    And I press tab
    And I press tab
    And I press tab
    And I press the enter key
    And I wait "1" seconds
    Then I should see "deleted"
    And I should not see "Testing template1"
    And I wait "2" seconds