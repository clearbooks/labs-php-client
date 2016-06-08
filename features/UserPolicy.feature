Feature: User policies

  Background:
    Given I am user "U"
    And I have toggles:
      | Toggle name            | Is released | Is visible | User toggle |
      | Accessible toggle      | No          | Yes        | No          |
      | User interface feature | No          | Yes        | Yes         |
      | Released toggle        | Yes         | Yes        | Yes         |
      | Hidden toggle          | Yes         | No         | Yes         |

  Scenario: User policy is enabled for user toggle
    Given user "U" has enabled user policy for toggle "User interface feature"
    Then toggle "User interface feature" is active

  Scenario: User policy is disabled for user toggle
    Given user "U" has disabled user policy for toggle "User interface feature"
    Then toggle "User interface feature" is not active

  Scenario: User policy is disabled for released user toggle
    Given user "U" has disabled user policy for toggle "Released toggle"
    Then toggle "Released toggle" is active

  Scenario: User policy is enabled for released, not visible user toggle
    Given user "U" has disabled user policy for toggle "Hidden toggle"
    Then toggle "Hidden toggle" is not active

  Scenario: User policy is not set for user toggle
    Then toggle "User interface feature" is not active

  Scenario: User policy is enabled for non-user toggle
    Given user "U" has enabled user policy for toggle "Accessible toggle"
    Then toggle "Accessible toggle" is not active

