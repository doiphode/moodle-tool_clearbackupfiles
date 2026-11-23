@tool @tool_clearbackupfiles
Feature: Choose a cutoff date for a manual backup cleanup
  In order to run a one-off cleanup without changing the plugin settings
  As an admin
  I need to choose an optional cutoff date on the manual cleanup page

  Scenario: The configured retention period is shown as the default
    Given the following config values are set as admin:
      | days | 5 | tool_clearbackupfiles |
    And I log in as "admin"
    When I navigate to "Courses > Backups > Clear backup files" in site administration
    Then I should see "Cutoff date"
    And I should see "at least 5 days old are deleted"

  Scenario: A chosen cutoff date is used for this run
    Given the following config values are set as admin:
      | days | 5 | tool_clearbackupfiles |
    And I log in as "admin"
    And I navigate to "Courses > Backups > Clear backup files" in site administration
    When I set the following fields to these values:
      | id_cutoffdate_enabled | 1       |
      | id_cutoffdate_day     | 1       |
      | id_cutoffdate_month   | January |
      | id_cutoffdate_year    | 2020    |
    And I press "Delete"
    Then I should see "There are no backup files created on or before"
    And I should see "1 January 2020"

  Scenario: A cutoff date in the future is rejected
    Given I log in as "admin"
    And I navigate to "Courses > Backups > Clear backup files" in site administration
    When I set the following fields to these values:
      | id_cutoffdate_enabled | 1    |
      | id_cutoffdate_year    | 2050 |
    And I press "Delete"
    Then I should see "The cutoff date must not be in the future."

  Scenario: A matching backup is previewed and then deleted
    Given the following config values are set as admin:
      | days | 5 | tool_clearbackupfiles |
    And a backup file "old_backup.mbz" created "10" days ago exists
    And a backup file "recent_backup.mbz" created "1" days ago exists
    And I log in as "admin"
    And I navigate to "Courses > Backups > Clear backup files" in site administration
    When I press "Delete"
    Then I should see "This permanently deletes 1 backup files"
    And I press "Continue"
    And I should see "old_backup.mbz"
    And I should not see "recent_backup.mbz"
    And I should see "In total 1 backup files were deleted"
