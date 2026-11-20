@tool @tool_clearbackupfiles
Feature: Explain the backup cleanup settings
  In order to configure the backup cleanup safely
  As an admin
  I need clear descriptions for the retention period and the scheduled cleanup

  Scenario: Admin sees descriptions for each cleanup setting
    Given I log in as "admin"
    When I navigate to "Plugins > Admin tools > Clear backup files" in site administration
    Then I should see "Retention period (days)"
    And I should see "manual cleanup page and the scheduled task"
    And I should see "Enable scheduled cleanup"
    And I should see "Clear backup (.mbz) files task"
