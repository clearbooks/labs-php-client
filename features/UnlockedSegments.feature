Feature: Unlocked segments

  Background:
    Given I am user "U"
    And I have toggles:
      | Toggle name            | Is released | Is visible | User toggle |
      | User interface feature | No          | Yes        | Yes         |
      | Invisible feature      | No          | No         | Yes         |
      | Accessible toggle      | No          | Yes        | No          |


  Scenario: Unlocked segment, where segment is active
    Given user "U" is in unlocked segment "Segment of users"
    And there is an active toggle policy "User interface feature" linked to "Segment of users"
    Then toggle "User interface feature" is active

  Scenario: Unlocked segment, where segment is inactive
    Given user "U" is in unlocked segment "Segment of users"
    And there is a inactive toggle policy "User interface feature" linked to "Segment of users"
    Then toggle "User interface feature" is not active

  Scenario: Unlocked segment, where segment is active, toggle is invisible
    Given user "U" is in unlocked segment "Segment of users"
    And there is an active toggle policy "Invisible feature" linked to "Segment of users"
    Then toggle "Invisible feature" is not active

  Scenario: User is in two locked segments for toggle, disabled on higher priority, enabled on lower priority
    Given user "U" is in unlocked segment "Segment of broken feature users"
    And user "U" is in unlocked segment "Segment of feature users"
    And there is a inactive toggle policy "Accessible toggle" linked to "Segment of broken feature users"
    And there is an active toggle policy "Accessible toggle" linked to "Segment of feature users"
    When the priority of "Segment of broken feature users" is 9001
    And the priority of "Segment of feature users" is 1
    Then toggle "Accessible toggle" is not active