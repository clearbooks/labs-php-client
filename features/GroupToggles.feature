Feature: Group toggle policies

  Background:
    Given I am user "U"
    And the current group is "G"
    And I have toggles:
      | Toggle name       | Is released | Is visible | Group toggle |
      | Unreleased toggle | No          | Yes        | Yes          |

  Scenario: Group policy is not set for group toggle
    Then toggle "Unreleased toggle" is not active

  Scenario: Group policy is enabled for group toggle
    Given group "G" has enabled group policy for toggle "Unreleased toggle"
    Then toggle "Unreleased toggle" is active

  Scenario: Group policy is disabled for group toggle
    Given group "G" has disabled group policy for toggle "Unreleased toggle"
    Then toggle "Unreleased toggle" is not active