Feature: Auto subscribe to User Toggles

  Background:
    Given I am user "U"
    And I have toggles:
      | Toggle name       | Is released | Is visible | User toggle |
      | Unreleased toggle | No          | Yes        | Yes         |
      | Released toggle   | Yes         | Yes        | Yes         |
      | Hidden toggle     | No          | No         | Yes         |
      | Group toggle      | No          | Yes        | No          |


  Scenario: Autosubscription is off, unreleased toggle
    Then toggle "Unreleased toggle" is not active

  Scenario: Autosubscription is on, unreleased toggle
    Given user "U" is autosubscribed
    Then toggle "Unreleased toggle" is active

  Scenario: Autosubscription is on, released toggle
    Given user "U" is autosubscribed
    Then toggle "Released toggle" is active

  Scenario: Autosubscription is on, invisible toggle
    Given user "U" is autosubscribed
    Then toggle "Hidden toggle" is not active

  Scenario: Autosubscription is on, unreleased toggle, non-user toggle
    Given user "U" is autosubscribed
    Then toggle "Group toggle" is not active