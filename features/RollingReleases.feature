Feature: Rolling releases

  Background:
    Given I have toggles:
      | Toggle name       | Is released | Is visible |
      | Top drawer toggle | Yes         | Yes        |
      | Best toggle       | No          | Yes        |
      | Scary toggle      | Yes         | No         |
      | Hidden toggle     | No          | No         |

  Scenario: Unreleased toggle is not active by default
    Then toggle "Best toggle" is not active

  Scenario: Released toggle is active
    Then toggle "Top drawer toggle" is active

  Scenario: Released but not visible toggle is not active by default
    Then toggle "Scary toggle" is not active

  Scenario: Unreleased not visible toggle is not active by default
    Then toggle "Hidden toggle" is not active
