@managerules_admin @notificationsagent @javascript
Feature: Testing rules management in notifications agent plugin
  In order to test the rule management
  As an admin
  I should be configure and manage rules in the notifications agent plugin.

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

  Scenario: Admin create rule for plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I wait "1" seconds
    And I click on "New rule" "link"
    And I set the following fields to these values:
      | Title | Testing rule1 |
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
    Then I should see "Rule saved"
    And I should see "Testing rule1"
    And I should see "Active"
    And I wait "2" seconds

  Scenario: Admin edit rule for plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I wait "1" seconds
    And I click on "New rule" "link"
    And I set the following fields to these values:
      | Title | Testing rule1 |
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
    And I should see "Rule saved"
    And I should see "Testing rule1"
    And I should see "Active"
    And I wait "2" seconds
    When I click on "Edit" "link"
    And I set the following fields to these values:
      | Title | rule1 EDITED NAME |
      | Message [BBBB] | Hi {User_Username}, EDITED message. |
    And I click on the input element with placeholder "Days" inside div with id "nav-conditions"
    And I type "0"
    And I click on "Save changes" "button"
    And I should see "Rule saved"
    Then I should see "rule1 EDITED NAME"
    And I should see "30 days has passed since the user last session in the course."
    And I wait "2" seconds

  Scenario: Admin delete rule for plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I wait "1" seconds
    And I click on "New rule" "link"
    And I set the following fields to these values:
      | Title | Testing rule1 |
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
    And I should see "Rule saved"
    And I should see "Testing rule1"
    And I should see "Active"
    And I wait "2" seconds
    When I click on "Delete" "link"
    And I wait "1" seconds
    And I press tab
    And I press tab
    And I press tab
    And I press the enter key
    And I wait "1" seconds
    Then I should see "Rule deleted"
    And I should not see "Active"
    And I should not see "Testing rule1"
    And I wait "2" seconds

  Scenario: Admin pause and reactivate rule for plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I wait "1" seconds
    And I click on "New rule" "link"
    And I set the following fields to these values:
      | Title | Testing rule1 |
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
    And I should see "Rule saved"
    And I should see "Testing rule1"
    And I should see "Active"
    And I wait "1" seconds
    When I click on "Pause" "link"
    And I wait "1" seconds
    And I click on "Pause" "button"
    And I wait "1" seconds
    Then I should see "Rule paused"
    And I should see "Paused"
    And I should not see "Active"
    And I click on "Activate" "link"
    And I wait "1" seconds
    And I click on "Activate" "button"
    And I wait "1" seconds
    And I should see "Rule activated"
    And I should see "Active"
    And I should not see "Paused"
    And I wait "2" seconds

  Scenario: Admin assign rule for plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I wait "1" seconds
    And I click on "New rule" "link"
    And I set the following fields to these values:
      | Title | Testing rule1 |
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
    And I should see "Rule saved"
    And I should see "Testing rule1"
    And I should see "Active"
    And I wait "2" seconds
    When I click on "Assign" "link"
    And I click on "checkboxcategory-1" "checkbox"
    And I click on "saveassignTemplateModal" "button"
    And I wait "2" seconds
    And I am on "testnotifagent" course homepage
    And I click on "More" if it exists otherwise "My assistant"
    Then I should see "Testing rule1"
    And I should see "Active"
    And I wait "2" seconds

  Scenario: Admin assign rule as forced in the plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I wait "1" seconds
    And I click on "New rule" "link"
    And I set the following fields to these values:
      | Title | Testing rule1 |
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
    And I should see "Rule saved"
    And I should see "Testing rule1"
    And I should see "Active"
    And I wait "2" seconds
    When I click on "Assign" "link"
    And I click on "checkboxcategory-1" "checkbox"
    And I click on "forced" "checkbox"
    And I click on "saveassignTemplateModal" "button"
    And I wait "2" seconds
    And I am on "testnotifagent" course homepage
    And I click on "More" if it exists otherwise "My assistant"
    Then I should see "Testing rule1"
    And I should see "Required"
    And I wait "2" seconds

  Scenario: Admin export rule for plugin notification agent
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I wait "1" seconds
    And I click on "New rule" "link"
    And I set the following fields to these values:
      | Title | Testing rule1 |
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
    And I should see "Rule saved"
    And I should see "Testing rule1"
    And I should see "Active"
    Then I click on "Export" "link"
    And I wait "3" seconds

  Scenario: Admin delete rule with assigned courses
    Given I click on "Site administration" "link"
    And I wait "1" seconds
    And I click on "Plugins" "link"
    And I wait "1" seconds
    And I click on "My assistant" "link"
    And I wait "1" seconds
    And I click on "New rule" "link"
    And I set the following fields to these values:
      | Title | Testing rule1 |
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
    And I should see "Rule saved"
    And I should see "Testing rule1"
    And I should see "Active"
    And I wait "2" seconds
    When I click on "Assign" "link"
    And I click on "checkboxcategory-1" "checkbox"
    And I click on "saveassignTemplateModal" "button"
    And I wait "1" seconds
    And I click on "Delete" "link"
    And I wait "1" seconds
    And I press tab
    And I press tab
    And I press tab
    And I press the enter key
    And I wait "1" seconds
    Then I should see "Rule deleted"
    And I should not see "Active"
    And I should not see "Testing rule1"
    And I wait "2" seconds