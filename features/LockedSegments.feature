Feature: Locked segments

  Background:
    Given I have toggles:
      | Toggle name         | Is released | Is visible | User toggle |
      | Inaccessible toggle | No          | No         | No          |
      | Accessible toggle   | No          | Yes        | No          |
      | Released toggle     | Yes         | Yes        | No          |
      | User toggle         | No          | Yes        | Yes         |
    And I am user "U"

  Scenario: User is in segment for toggle, but toggle is not visible
    Given user "U" is in locked segment "Segment of users"
    And there is an active toggle policy "Inaccessible toggle" linked to "Segment of users"
    Then toggle "Inaccessible toggle" is not active

  Scenario: User is in segment for toggle, but toggle is visible
    Given user "U" is in locked segment "Segment of users"
    And there is an active toggle policy "Accessible toggle" linked to "Segment of users"
    Then toggle "Accessible toggle" is active

  Scenario: User is in locked segment for toggle, with disabled policy, but toggle is released
    Given user "U" is in locked segment "Segment of users"
    And there is a inactive toggle policy "Released toggle" linked to "Segment of users"
    Then toggle "Released toggle" is active

  Scenario: User is in locked segment for toggle, with disabled policy, but toggle is released
    Given user "U" is in locked segment "Segment of users"
    And there is a inactive toggle policy "Accessible toggle" linked to "Segment of users"
    Then toggle "Accessible toggle" is not active

  Scenario: User is in two locked segments for toggle, disabled on higher priority, enabled on lower priority
    Given user "U" is in locked segment "Segment of broken feature users"
    And user "U" is in locked segment "Segment of feature users"
    And there is a inactive toggle policy "Accessible toggle" linked to "Segment of broken feature users"
    And there is an active toggle policy "Accessible toggle" linked to "Segment of feature users"
    When the priority of "Segment of broken feature users" is 9001
    And the priority of "Segment of feature users" is 1
    Then toggle "Accessible toggle" is not active

  Scenario: User is in two locked segments for toggle, enabled on higher priority, disabled on lower priority
    Given user "U" is in locked segment "Segment of scared users"
    And user "U" is in locked segment "Segment of hot fix users"
    And there is a inactive toggle policy "Accessible toggle" linked to "Segment of scared users"
    And there is an active toggle policy "Accessible toggle" linked to "Segment of hot fix users"
    When the priority of "Segment of scared users" is 1
    And the priority of "Segment of hot fix users" is 9000
    Then toggle "Accessible toggle" is active

  Scenario: User policy is enabled for visible user toggle, for locked inactive segment
    Given user "U" has enabled user policy for toggle "User toggle"
    And user "U" is in locked segment "Segment of users"
    And there is a inactive toggle policy "User toggle" linked to "Segment of users"
    Then toggle "User toggle" is not active