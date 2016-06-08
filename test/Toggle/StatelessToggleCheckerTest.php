<?php
namespace Clearbooks\Labs\Client\Toggle;

use Clearbooks\Labs\Client\Toggle\Entity\Group;
use Clearbooks\Labs\Client\Toggle\Entity\GroupStub;
use Clearbooks\Labs\Client\Toggle\Entity\Segment;
use Clearbooks\Labs\Client\Toggle\Entity\SegmentStub;
use Clearbooks\Labs\Client\Toggle\Entity\User;
use Clearbooks\Labs\Client\Toggle\Entity\UserStub;
use Clearbooks\Labs\Client\Toggle\Gateway\AutoSubscribersGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\BaseTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\GroupTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\SegmentTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\ToggleGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\UserTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentLockedPropertyFilter;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentPolicyEvaluator;
use Clearbooks\Labs\Client\Toggle\Segment\SegmentPriorityArranger;
use Clearbooks\Labs\Client\Toggle\UseCase\IsCurrentUserToggleActive;

class StatelessToggleCheckerTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TOGGLE = "test toggle";

    /** @var BaseTogglePolicyGatewayMock */
    private $groupPolicyGatewayMock;

    /** @var BaseTogglePolicyGatewayMock */
    private $userPolicyGatewayMock;

    /** @var ToggleGatewayMock */
    private $toggleGatewayMock;

    /** @var AutoSubscribersGatewayMock */
    private $autoSubscribersGatewayMock;

    /** @var SegmentTogglePolicyGatewayMock */
    private $segmentPolicyGatewayMock;

    /** @var StatelessToggleChecker */
    private $statelessToggleChecker;

    /** @var IsCurrentUserToggleActive */
    private $currentUserToggleChecker;

    /** @var User */
    private $currentUser;

    /** @var Group */
    private $currentGroup;

    /** @var int */
    private $nextSegmentId = 0;


    private function setToggleVisible()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
    }

    private function setToggleReleased()
    {
        $this->toggleGatewayMock->setIsReleaseDateTodayOrInThePast( self::TEST_TOGGLE, true );
    }

    private function assertToggleIsActive()
    {
        $this->assertTrue( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    private function assertToggleIsInactive()
    {
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    private function setToggleInvisible()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, false );
    }

    private function setToggleIsInEnabledLockedSegment()
    {
        $lockedSegment = $this->createSegment( 10, true  );
        $this->segmentPolicyGatewayMock->setTogglePolicyEnabled(
            self::TEST_TOGGLE,
            $lockedSegment
        );
        $this->createCurrentToggleCheckerWithSegments(
            [ $lockedSegment ]
        );
    }

    /**
     * @param $priority
     * @param $locked
     * @return SegmentStub
     */
    private function createSegment( $priority, $locked )
    {
        return new SegmentStub( $this->nextSegmentId++, $priority, $locked );
    }

    /**
     * @param $priority
     * @param $togglePolicyEnabled
     * @return SegmentStub
     */
    private function setLockedSegmentPolicy( $priority, $togglePolicyEnabled )
    {
        $segment = $this->createSegment( $priority, true );
        $this->setSegmentPolicy( $togglePolicyEnabled, $segment );
        return $segment;
    }

    /**
     * @param $priority
     * @param $togglePolicyEnabled
     * @return SegmentStub
     */
    private function setUnlockedSegmentPolicy( $priority, $togglePolicyEnabled )
    {
        $segment = $this->createSegment( $priority, false );
        $this->setSegmentPolicy( $togglePolicyEnabled, $segment );
        return $segment;
    }

    /**
     * @param $togglePolicyEnabled
     * @param $segment
     */
    private function setSegmentPolicy( $togglePolicyEnabled, $segment )
    {
        if ( $togglePolicyEnabled ) {
            $this->segmentPolicyGatewayMock->setTogglePolicyEnabled(
                self::TEST_TOGGLE,
                $segment
            );
        } else {
            $this->segmentPolicyGatewayMock->setTogglePolicyDisabled(
                self::TEST_TOGGLE,
                $segment
            );
        }
    }

    private function setUserPolicyEnabled()
    {
        $this->userPolicyGatewayMock->setTogglePolicyEnabled(
            self::TEST_TOGGLE,
            $this->currentUser
        );
    }

    private function setUserPolicyDisabled()
    {
        $this->userPolicyGatewayMock->setTogglePolicyDisabled(
            self::TEST_TOGGLE,
            $this->currentUser
        );
    }

    private function setIsGroupToggle()
    {
        $this->toggleGatewayMock->setIsGroupToggle( self::TEST_TOGGLE, true );
    }

    private function setIsNotGroupToggle()
    {
        $this->toggleGatewayMock->setIsGroupToggle( self::TEST_TOGGLE, false );
    }

    private function setGroupPolicyEnabled()
    {
        $this->groupPolicyGatewayMock->setTogglePolicyEnabled(
            self::TEST_TOGGLE,
            $this->currentGroup
        );
    }

    private function setGroupPolicyDisabled()
    {
        $this->groupPolicyGatewayMock->setTogglePolicyDisabled(
            self::TEST_TOGGLE,
            $this->currentGroup
        );
    }

    private function setUserAutoSubscribed()
    {
        $this->autoSubscribersGatewayMock->setUserSubscriberStatus( $this->currentUser, true );
    }

    /**
     * @param Segment[] $segments
     */
    private function createCurrentToggleCheckerWithSegments( array $segments )
    {
        $this->currentUserToggleChecker = new CurrentUserToggleChecker(
            $this->currentUser,
            $this->currentGroup,
            $segments,
            $this->statelessToggleChecker
        );
    }

    public function setUp()
    {
        parent::setUp();
        $this->currentUser = new UserStub( 1 );
        $this->currentGroup = new GroupStub( 2 );

        $this->toggleGatewayMock = new ToggleGatewayMock();
        $this->groupPolicyGatewayMock = new GroupTogglePolicyGatewayMock();
        $this->userPolicyGatewayMock = new UserTogglePolicyGatewayMock();
        $this->autoSubscribersGatewayMock = new AutoSubscribersGatewayMock();
        $this->segmentPolicyGatewayMock = new SegmentTogglePolicyGatewayMock();
        $this->statelessToggleChecker = new StatelessToggleChecker(
                $this->toggleGatewayMock, $this->userPolicyGatewayMock,
                $this->groupPolicyGatewayMock, $this->autoSubscribersGatewayMock,
                $this->segmentPolicyGatewayMock, new SegmentLockedPropertyFilter(),
                new SegmentPolicyEvaluator( new SegmentPriorityArranger(), $this->segmentPolicyGatewayMock )
        );

        $this->createCurrentToggleCheckerWithSegments( [ ] );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndNotSetByPolicyButReleaseDateIsTodayOrInThePast_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setToggleReleased();

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleNotVisibleAndNotSetByPolicyButReleaseDateIsTodayOrInThePast_ThenExpectInactive()
    {
        $this->setToggleReleased();

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndReleaseDateIsInTheFuture_ThenExpectInactive()
    {
        $this->setToggleVisible();

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleIsNotVisibleAndReleaseDateIsInThePastAndInEnabledLockedSegment_ThenExpectInactive()
    {
        $this->setToggleInvisible();
        $this->setToggleReleased();
        $this->setToggleIsInEnabledLockedSegment();

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleIsVisible_WhenLockedSegmentPolicyIsEnabled_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setToggleIsInEnabledLockedSegment();

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleIsVisible_AndHighPriorityDisabledSegmentPolicyIsSet_ThenExpectToggleToBeInactive()
    {
        $this->setToggleVisible();

        $lockedSegment1 = $this->setLockedSegmentPolicy( 9000, true );
        $lockedSegment2 = $this->setLockedSegmentPolicy( 10, false );
        $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment2, $lockedSegment1 ] );

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndEnabledByUserPolicyAndReleaseDateIsInTheFuture_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setUserPolicyEnabled();

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndDisabledByUserPolicyAndReleaseDateIsInTheFuture_ThenExpectInactive()
    {
        $this->setToggleVisible();
        $this->setUserPolicyDisabled();

        $this->assertToggleIsInactive();
    }


    /**
     * @test
     */
    public function GivenGroupToggleVisibleAndEnabledByUserPolicyAndReleaseDateIsInTheFuture_ThenExpectInactive()
    {
        $this->setToggleVisible();
        $this->setIsGroupToggle();
        $this->setUserPolicyEnabled();

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenUserToggleVisibleAndEnabledByUserPolicyAndReleaseDateIsInTheFuture_ThenExpectInactive()
    {
        $this->setToggleVisible();
        $this->setIsNotGroupToggle();
        $this->setUserPolicyEnabled();

        $lockedSegment = $this->setLockedSegmentPolicy( 10, false );
        $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenUserToggleVisibleAndGroupPolicyIsEnabled_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setGroupPolicyEnabled();

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenGroupToggleVisibleAndGroupPolicyIsEnabled_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setIsGroupToggle();
        $this->setGroupPolicyEnabled();

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndGroupPolicyIsDisabled_ThenExpectInactive()
    {
        $this->setToggleVisible();
        $this->setGroupPolicyDisabled();

        $this->assertToggleIsInactive();
    }


    /**
     * @test
     */
    public function GivenToggleVisibleAndUnlockedSegmentPolicyEnabled_ThenExpectActive()
    {
        $this->setToggleVisible();

        $unlockedSegment = $this->setUnlockedSegmentPolicy( 10, true );
        $this->createCurrentToggleCheckerWithSegments( [ $unlockedSegment ] );

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndSegmentPolicyEnabled_WhenPassingAdditionalUnsetSegment_ThenExpectActive()
    {
        $this->setToggleVisible();

        $unlockedSegment1 = $this->setUnlockedSegmentPolicy( 10, true );
        $unlockedSegment2 = $this->createSegment( 15, false );

        $this->createCurrentToggleCheckerWithSegments( [ $unlockedSegment1, $unlockedSegment2 ] );
        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndSegmentPolicyDisabled_ThenExpectInActive()
    {
        $this->setToggleVisible();

        $unlockedSegment = $this->setUnlockedSegmentPolicy( 10, false );
        $this->createCurrentToggleCheckerWithSegments( [ $unlockedSegment ] );

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndSegmentPolicyDisabledAndUserAutoSubscribed_ThenExpectInActive()
    {
        $this->setToggleVisible();
        $this->setUserAutoSubscribed();

        $unlockedSegment = $this->setUnlockedSegmentPolicy( 10, false );
        $this->createCurrentToggleCheckerWithSegments( [ $unlockedSegment ] );

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndUserAutoSubscribed_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setUserAutoSubscribed();

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndMultipleNonLockedSegmentPoliciesAreSet_ThenSegmentPrioritiesDecides()
    {
        $this->setToggleVisible();
        $unlockedSegment1 = $this->setUnlockedSegmentPolicy( 10, false );
        $unlockedSegment2 = $this->setUnlockedSegmentPolicy( 100, true );
        $this->createCurrentToggleCheckerWithSegments( [ $unlockedSegment1, $unlockedSegment2 ] );

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndMultipleNonLockedSegmentPoliciesAreSetWithSamePriority_ThenTheHighestIdSegmentDecides()
    {
        $this->setToggleVisible();
        $unlockedSegment1 = $this->setUnlockedSegmentPolicy( 10, false );
        $unlockedSegment2 = $this->setUnlockedSegmentPolicy( 10, true );
        $this->createCurrentToggleCheckerWithSegments( [ $unlockedSegment1, $unlockedSegment2 ] );

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndIsGroupToggleAndIsInEnabledSegment_ThenReturnIsActive()
    {
        $this->setToggleVisible();
        $this->setIsGroupToggle();

        $unlockedSegment1 = $this->createSegment( 10, false );
        $unlockedSegment2 = $this->setUnlockedSegmentPolicy( 15, true );
        $this->createCurrentToggleCheckerWithSegments( [ $unlockedSegment1, $unlockedSegment2 ] );

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndIsGroupToggleAndGroupToggleIsDisabledAndIsInEnabledSegment_ThenReturnIsActive()
    {
        $this->setToggleVisible();
        $this->setIsGroupToggle();
        $this->setGroupPolicyDisabled();

        $unlockedSegment1 = $this->createSegment( 10, false );
        $unlockedSegment2 = $this->setUnlockedSegmentPolicy( 15, true );
        $this->createCurrentToggleCheckerWithSegments( [ $unlockedSegment1, $unlockedSegment2 ] );

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndEnabledByUserPolicyAndReleaseDateIsInTheFutureAndUserIsNotAutoSubscribed_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setUserPolicyEnabled();

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleNotVisibleAndNotSetByPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectInactive()
    {
        $this->setUserAutoSubscribed();

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenGroupToggleVisibleButNotSetByPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectInactive()
    {
        $this->setToggleVisible();
        $this->setIsGroupToggle();
        $this->setUserAutoSubscribed();

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndDisabledByUserPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectInactive()
    {
        $this->setToggleVisible();
        $this->setUserPolicyDisabled();
        $this->setUserAutoSubscribed();

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndNotSetByUserPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setUserAutoSubscribed();

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndEnabledByUserPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setUserPolicyEnabled();
        $this->setUserAutoSubscribed();

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleNotVisible_ThenExpectInactive()
    {
        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndGroupPolicyIsNotSetAndUserPolicyIsNotSet_ThenExpectInactive()
    {
        $this->setToggleVisible();

        $this->assertToggleIsInactive();
    }


    /**
     * @test
     */
    public function GivenToggleVisibleAndGroupPolicyIsNotSetAndUserPolicyIsDisabled_ThenExpectInactive()
    {
        $this->setToggleVisible();
        $this->setUserPolicyDisabled();

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndGroupPolicyIsNotSetAndUserPolicyIsEnabled_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setUserPolicyEnabled();

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndToggleTypeIsGroupAndGroupPolicyIsNotSetAndUserPolicyIsEnabled_ThenExpectInactive()
    {
        $this->setToggleVisible();
        $this->setIsGroupToggle();
        $this->setUserPolicyEnabled();

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndToggleTypeIsGroupAndGroupPolicyIsDisabledAndUserPolicyIsEnabled_ThenExpectInactive()
    {
        $this->setToggleVisible();
        $this->setIsGroupToggle();
        $this->setGroupPolicyDisabled();
        $this->setUserPolicyEnabled();

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndToggleTypeIsGroupAndGroupPolicyIsEnabledAndUserPolicyIsEnabled_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setIsGroupToggle();
        $this->setGroupPolicyEnabled();
        $this->setUserPolicyEnabled();

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenGroupToggleVisibleAndNotSetByGroupPolicy_WhenLockedSegmentPolicyIsEnabled_ThenExpectInactive()
    {
        $this->setToggleVisible();
        $this->setIsGroupToggle();

        $lockedSegment = $this->setLockedSegmentPolicy( 10, false );
        $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndDisabledByGroupPolicy_WhenLockedSegmentPolicyIsEnabled_ThenExpectActive()
    {
        $this->setToggleVisible();
        $this->setGroupPolicyDisabled();

        $lockedSegment = $this->setLockedSegmentPolicy( 10, true );
        $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndEnabledByGroupPolicy_WhenLockedSegmentPolicyIsDisabled_ThenExpectInactive()
    {
        $this->setToggleVisible();
        $this->setGroupPolicyEnabled();

        $lockedSegment = $this->setLockedSegmentPolicy( 10, false );
        $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );

        $this->assertToggleIsInactive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndMultipleLockedSegmentsAreSet_ThenSegmentPriorityDecides()
    {
        $this->setToggleVisible();

        $lockedSegment1 = $this->setLockedSegmentPolicy( 10, false );
        $lockedSegment2 = $this->setLockedSegmentPolicy( 15, true );
        $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment1, $lockedSegment2 ] );

        $this->assertToggleIsActive();
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndMultipleSegmentsAreSetIncludingLockedOnes_ThenSegmentPriorityDecidesBetweenTheLockedOnes()
    {
        $this->setToggleVisible();

        $lockedSegment1 = $this->setLockedSegmentPolicy( 5, true );
        $lockedSegment2 = $this->setLockedSegmentPolicy( 20, false );
        $unlockedSegment3 = $this->setUnlockedSegmentPolicy( 100, true );

        $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment1, $lockedSegment2, $unlockedSegment3 ] );

        $this->assertToggleIsInactive();
    }

}
