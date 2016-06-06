<?php

use Behat\Gherkin\Node\TableNode;
use Clearbooks\Labs\Client\Toggle\Entity\GroupStub;
use Clearbooks\Labs\Client\Toggle\Entity\SegmentStub;
use Clearbooks\Labs\Client\Toggle\Entity\UserStub;
use Clearbooks\Labs\Client\Toggle\Gateway\AutoSubscribersGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\GroupTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\SegmentTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\ToggleGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\UserTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentLockedPropertyFilter;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentPolicyEvaluator;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentPriorityArranger;
use Clearbooks\Labs\Client\Toggle\StatelessToggleChecker;

class LabsTogglesContext implements \Behat\Behat\Context\Context
{

    /** @var  GroupTogglePolicyGatewayMock */
    private $groupPolicyGateway;

    /** @var UserTogglePolicyGatewayMock */
    private $userPolicyGateway;

    /** @var ToggleGatewayMock */
    private $toggleGateway;

    /** @var SegmentTogglePolicyGatewayMock */
    private $segmentTogglePolicyGateway;

    /** @var SegmentStub[] */
    private $nextSegments = [];

    /** @var string */
    private $userName;
    /** @var string */
    private $currentGroup;

    /**
     * @BeforeScenario
     */
    public function init()
    {
        $this->toggleGateway = new ToggleGatewayMock();
        $this->segmentTogglePolicyGateway = new SegmentTogglePolicyGatewayMock();
        $this->userPolicyGateway = new UserTogglePolicyGatewayMock();
        $this->groupPolicyGateway = new GroupTogglePolicyGatewayMock();
    }

    /**
     * @param $toggleName
     * @return bool
     */
    private function isToggleActive( $toggleName )
    {

        $toggleChecker = new StatelessToggleChecker(
            $this->toggleGateway,
            $this->userPolicyGateway,
            $this->groupPolicyGateway,
            new AutoSubscribersGatewayMock(), $this->segmentTogglePolicyGateway,
            new SegmentLockedPropertyFilter(),
            new SegmentPolicyEvaluator(
                new SegmentPriorityArranger(), $this->segmentTogglePolicyGateway
            )
        );

        $isToggleActive = $toggleChecker->isToggleActive(
            $toggleName,
            new UserStub( $this->userName ),
            new GroupStub( $this->currentGroup ),
            $this->nextSegments
        );
        return $isToggleActive;
    }

    /**
     * @param string $maybeYes
     * @return bool
     */
    private function yesToBool( $maybeYes )
    {
        return $maybeYes === 'Yes';
    }

    /**
     * @Given /^I have toggles:$/
     * @param TableNode $table
     */
    public function iHaveToggles( TableNode $table )
    {

        foreach ( $table as $row ) {
            $this->toggleGateway->setIsReleaseDateTodayOrInThePast(
                $row['Toggle name'],
                $this->yesToBool( $row['Is released'] )
            );
            $this->toggleGateway->setVisibility(
                $row['Toggle name'],
                $this->yesToBool( $row['Is visible'] )
            );

            $this->toggleGateway->setIsGroupToggle(
                $row['Toggle name'],
                $this->isGroupToggleFromTableRowItem( $row )
            );
        }

        foreach ( $table as $row ) {
            PHPUnit_Framework_Assert::assertEquals(
                $this->yesToBool( $row['Is released'] ),
                $this->toggleGateway->isReleaseDateOfToggleReleaseTodayOrInThePast(
                    $row['Toggle name']
                )
            );
            PHPUnit_Framework_Assert::assertEquals(
                $this->yesToBool( $row['Is visible'] ),
                $this->toggleGateway->isToggleVisibleForUsers(
                    $row['Toggle name']
                )
            );
            PHPUnit_Framework_Assert::assertEquals(
                $this->isGroupToggleFromTableRowItem( $row ),
                $this->toggleGateway->isGroupToggle(
                    $row['Toggle name']
                )
            );
        }
    }

    /**
     * @Then /^toggle "([^"]*)" is not active$/
     */
    public function toggleIsNotActive( $toggleName )
    {
        $isToggleActive = $this->isToggleActive( $toggleName );
        PHPUnit_Framework_Assert::assertFalse( $isToggleActive );
    }

    /**
     * @Then /^toggle "([^"]*)" is active$/
     */
    public function toggleIsActive( $toggleName )
    {
        $isToggleActive = $this->isToggleActive( $toggleName );
        PHPUnit_Framework_Assert::assertTrue( $isToggleActive );
    }
    
    /**
     * @Given /^user "([^"]*)" is in locked segment "([^"]*)"$/
     */
    public function userIsInSegment( $_unused, $segmentId )
    {
        $this->nextSegments[ $segmentId ] = new SegmentStub( $segmentId, 10, true );
    }
    
    /**
     * @Given /^there is an active toggle policy "([^"]*)" linked to "([^"]*)"$/
     */
    public function thereIsATogglePolicyAnd( $toggleName, $segmentId )
    {
        $this->segmentTogglePolicyGateway->setTogglePolicyEnabled( $toggleName, new SegmentStub( $segmentId, 10, true ) );
        $togglePolicyResponse = $this->segmentTogglePolicyGateway->getTogglePolicy( $toggleName, new SegmentStub( $segmentId, 10, true ) );
        PHPUnit_Framework_Assert::assertTrue( $togglePolicyResponse->isEnabled() );
        PHPUnit_Framework_Assert::assertFalse( $togglePolicyResponse->isNotSet() );
    }

    /**
     * @Given /^there is a inactive toggle policy "([^"]*)" linked to "([^"]*)"$/
     */
    public function thereIsAInactiveTogglePolicyLinkedTo( $toggleName, $segmentId )
    {
        $this->segmentTogglePolicyGateway->setTogglePolicyDisabled( $toggleName, new SegmentStub( $segmentId, 10, true ) );
        $togglePolicyResponse = $this->segmentTogglePolicyGateway->getTogglePolicy( $toggleName, new SegmentStub( $segmentId, 10, true ) );
        PHPUnit_Framework_Assert::assertFalse( $togglePolicyResponse->isEnabled() );
        PHPUnit_Framework_Assert::assertFalse( $togglePolicyResponse->isNotSet() );
    }

    /**
     * @When /^the priority of "([^"]*)" is (\d+)$/
     */
    public function thePriorityOfIs( $segmentId, $priority )
    {
        $this->nextSegments[$segmentId]->setPriority( $priority );
    }

    /**
     * @Given /^user "([^"]*)" has enabled user policy for toggle "([^"]*)"$/
     */
    public function userHasEnabledUserPolicyForToggle( $userName, $toggleName )
    {
        $this->userPolicyGateway->setTogglePolicyEnabled( $toggleName, new UserStub( $userName ) );
    }

    /**
     * @Given /^user "([^"]*)" has disabled user policy for toggle "([^"]*)"$/
     */
    public function userHasDisabledUserPolicyForToggle( $userName, $toggleName )
    {
        $this->userPolicyGateway->setTogglePolicyDisabled( $toggleName, new UserStub( $userName ) );
    }

    /**
     * @Given /^I am user "([^"]*)"$/
     */
    public function iAmUser( $userName )
    {
        $this->userName = $userName;
    }

    /**
     * @param $row
     * @return bool
     */
    private function isGroupToggleFromTableRowItem( $row )
    {
        if ( !empty( $row['User toggle'] ) ) {
            return !$this->yesToBool( $row['User toggle'] );
        } else if( isset( $row['Group toggle'] ) ) {
            return $this->yesToBool( $row['Group toggle'] );
        }
        return false;
    }

    /**
     * @Given /^group "([^"]*)" has enabled group policy for toggle "([^"]*)"$/
     */
    public function groupHasEnabledGroupPolicyForToggle( $groupName, $toggleName )
    {
        $this->groupPolicyGateway->setTogglePolicyEnabled( $toggleName, new GroupStub( $groupName ) );
    }

    /**
     * @Given /^group "([^"]*)" has disabled group policy for toggle "([^"]*)"$/
     */
    public function groupHasDisabledGroupPolicyForToggle( $groupId, $toggleName )
    {
        $this->groupPolicyGateway->setTogglePolicyDisabled( $toggleName, new GroupStub( $groupId ) );
    }

    /**
     * @Given /^user "([^"]*)" is in unlocked segment "([^"]*)"$/
     */
    public function userIsInUnlockedSegment( $userName, $segmentId )
    {
        $this->nextSegments[$segmentId] = new SegmentStub( $segmentId, 10, false );
    }

    /**
     * @Given /^the current group is "([^"]*)"$/
     */
    public function theCurrentGroupIs( $groupId )
    {
        $this->currentGroup = $groupId;
    }

    
}