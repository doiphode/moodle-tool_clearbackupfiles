@tool @tool_clearbackupfiles
Feature: Open the backup cleanup tool from the Backups admin category
  In order to find the backup cleanup tool
  As an admin
  I need a navigation entry under Site administration > Courses > Backups

  Scenario: Admin opens the cleanup tool from the Backups category
    Given I log in as "admin"
    When I navigate to "Courses > Backups > Clear backup files" in site administration
    Then I should see "Clear backup files" in the "region-main" "region"
    And I should see "clearing of backup files is an irreversible process"
