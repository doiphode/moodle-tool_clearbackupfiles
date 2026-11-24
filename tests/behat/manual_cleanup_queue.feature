@tool @tool_clearbackupfiles
Feature: Run a manual backup cleanup in the background
  In order to clean up large backup volumes without blocking the browser
  As an admin
  I need to queue the manual cleanup as an ad hoc task

  Scenario: A queued cleanup deletes the files with the next cron run
    Given the following config values are set as admin:
      | days | 5 | tool_clearbackupfiles |
    And a backup file "old_backup.mbz" created "10" days ago exists
    And a backup file "recent_backup.mbz" created "1" days ago exists
    And I log in as "admin"
    And I navigate to "Courses > Backups > Clear backup files" in site administration
    When I set the field "In the background (ad hoc task)" to "1"
    And I press "Delete"
    Then I should see "A background task will permanently delete up to 1 backup files"
    And I press "Continue"
    And I should see "The cleanup was queued as a background task"
    And I navigate to "Courses > Backups > Clear backup files" in site administration
    And I press "Delete"
    And I should see "This permanently deletes 1 backup files"
    And I run all adhoc tasks
    And I navigate to "Courses > Backups > Clear backup files" in site administration
    And I press "Delete"
    And I should see "There are no backup files created on or before"

  Scenario: Only one background cleanup can be queued at a time
    Given a backup file "old_backup.mbz" created "10" days ago exists
    And I log in as "admin"
    And I navigate to "Courses > Backups > Clear backup files" in site administration
    And I set the field "In the background (ad hoc task)" to "1"
    And I press "Delete"
    And I press "Continue"
    And I should see "The cleanup was queued as a background task"
    When I navigate to "Courses > Backups > Clear backup files" in site administration
    And I set the field "In the background (ad hoc task)" to "1"
    And I press "Delete"
    And I press "Continue"
    Then I should see "A background cleanup is already queued or waiting for a retry"
