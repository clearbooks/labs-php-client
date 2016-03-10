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

class IsCurrentUserToggleActiveTest extends \PHPUnit_Framework_TestCase
{
    const TEST_TOGGLE = "test toggle";

    /**
     * @var BaseTogglePolicyGatewayMock
     */
    private $groupPolicyGatewayMock;

    /**
     * @var BaseTogglePolicyGatewayMock
     */
    private $userPolicyGatewayMock;

    /**
     * @var ToggleGatewayMock
     */
    private $toggleGatewayMock;

    /**
     * @var AutoSubscribersGatewayMock
     */
    private $autoSubscribersGatewayMock;

    /**
     * @var SegmentTogglePolicyGatewayMock
     */
    private $segmentPolicyGatewayMock;

    /**
     * @var StatelessToggleChecker
     */
    private $statelessToggleChecker;

    /**
     * @var IsCurrentUserToggleActive
     */
    private $currentUserToggleChecker;

    /**
     * @var User
     */
    private $currentUser;

    /**
     * @var Group
     */
    private $currentGroup;

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

        $this->currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ ] );
    }

    /**
     * @param Segment[] $segments
     * @return CurrentUserToggleChecker
     */
    private function createCurrentToggleCheckerWithSegments( array $segments )
    {
        return new CurrentUserToggleChecker(
                $this->currentUser,
                $this->currentGroup,
                $segments,
                $this->statelessToggleChecker
        );
    }

    /**
     * @test
     */
    public function GivenToggleNotVisible_ThenExpectInactive()
    {
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndGroupPolicyIsNotSetAndUserPolicyIsNotSet_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndGroupPolicyIsDisabled_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->groupPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $this->currentGroup );
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndGroupPolicyIsEnabled_ThenExpectActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->groupPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentGroup );
        $this->assertTrue( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndGroupPolicyIsNotSetAndUserPolicyIsDisabled_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->userPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $this->currentUser );
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndGroupPolicyIsNotSetAndUserPolicyIsEnabled_ThenExpectActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->userPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentUser );
        $this->assertTrue( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndToggleTypeIsGroupAndGroupPolicyIsNotSetAndUserPolicyIsEnabled_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->toggleGatewayMock->setIsGroupToggle( self::TEST_TOGGLE, true );
        $this->userPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentUser );
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndToggleTypeIsGroupAndGroupPolicyIsDisabledAndUserPolicyIsEnabled_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->toggleGatewayMock->setIsGroupToggle( self::TEST_TOGGLE, true );
        $this->groupPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $this->currentGroup );
        $this->userPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentUser );
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndToggleTypeIsGroupAndGroupPolicyIsEnabledAndUserPolicyIsEnabled_ThenExpectActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->toggleGatewayMock->setIsGroupToggle( self::TEST_TOGGLE, true );
        $this->groupPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentGroup );
        $this->userPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentUser );
        $this->assertTrue( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndNotSetByPolicyButReleaseDateIsTodayOrInThePast_ThenExpectActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->toggleGatewayMock->setIsReleaseDateTodayOrInThePast( self::TEST_TOGGLE, true );
        $this->assertTrue( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleNotVisibleAndNotSetByPolicyButReleaseDateIsTodayOrInThePast_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setIsReleaseDateTodayOrInThePast( self::TEST_TOGGLE, true );
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleNotVisibleAndNotSetByPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectInactive()
    {
        $this->autoSubscribersGatewayMock->setUserSubscriberStatus( $this->currentUser, true );
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenGroupToggleVisibleButNotSetByPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->toggleGatewayMock->setIsGroupToggle( self::TEST_TOGGLE, true );
        $this->autoSubscribersGatewayMock->setUserSubscriberStatus( $this->currentUser, true );
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndDisabledByUserPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->userPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $this->currentUser );
        $this->autoSubscribersGatewayMock->setUserSubscriberStatus( $this->currentUser, true );
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndEnabledByUserPolicyAndReleaseDateIsInTheFutureAndUserIsNotAutoSubscribed_ThenExpectActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->userPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentUser );
        $this->assertTrue( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndNotSetByUserPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->autoSubscribersGatewayMock->setUserSubscriberStatus( $this->currentUser, true );
        $this->assertTrue( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndEnabledByUserPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->userPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentUser );
        $this->autoSubscribersGatewayMock->setUserSubscriberStatus( $this->currentUser, true );
        $this->assertTrue( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenGroupToggleVisibleAndNotSetByGroupPolicy_WhenLockedSegmentPolicyIsEnabled_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->toggleGatewayMock->setIsGroupToggle( self::TEST_TOGGLE, true );

        $lockedSegment = new SegmentStub( 1, 10, true );
        $this->segmentPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $lockedSegment );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );
        $this->assertFalse( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenGroupToggleVisibleAndEnabledByGroupPolicy_WhenLockedSegmentPolicyIsDisabled_ThenExpectActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->toggleGatewayMock->setIsGroupToggle( self::TEST_TOGGLE, true );
        $this->groupPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentGroup );

        $lockedSegment = new SegmentStub( 1, 10, true );
        $this->segmentPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $lockedSegment );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );
        $this->assertTrue( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenGroupToggleVisibleAndDisabledByGroupPolicy_WhenLockedSegmentPolicyIsEnabled_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->toggleGatewayMock->setIsGroupToggle( self::TEST_TOGGLE, true );
        $this->groupPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $this->currentGroup );

        $lockedSegment = new SegmentStub( 1, 10, true );
        $this->segmentPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $lockedSegment );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );
        $this->assertFalse( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndDisabledByGroupPolicy_WhenLockedSegmentPolicyIsEnabled_ThenExpectActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->groupPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $this->currentGroup );

        $lockedSegment = new SegmentStub( 1, 10, true );
        $this->segmentPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $lockedSegment );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );
        $this->assertTrue( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndEnabledByGroupPolicy_WhenLockedSegmentPolicyIsDisabled_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->groupPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentGroup );

        $lockedSegment = new SegmentStub( 1, 10, true );
        $this->segmentPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $lockedSegment );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );
        $this->assertFalse( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndMultipleLockedSegmentsAreSet_ThenSegmentPriorityDecides()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );

        $lockedSegment1 = new SegmentStub( 1, 10, true );
        $lockedSegment2 = new SegmentStub( 2, 15, true );
        $this->segmentPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $lockedSegment1 );
        $this->segmentPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $lockedSegment2 );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment1, $lockedSegment2 ] );
        $this->assertTrue( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndMultipleSegmentsAreSetIncludingLockedOnes_ThenSegmentPriorityDecidesBetweenTheLockedOnes()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );

        $lockedSegment1 = new SegmentStub( 1, 5, true );
        $lockedSegment2 = new SegmentStub( 2, 20, true );
        $lockedSegment3 = new SegmentStub( 3, 100 );
        $this->segmentPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $lockedSegment1 );
        $this->segmentPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $lockedSegment2 );
        $this->segmentPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $lockedSegment3 );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment1, $lockedSegment2 ] );
        $this->assertFalse( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndSegmentPolicyEnabled_ThenExpectActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );

        $lockedSegment = new SegmentStub( 1, 10 );
        $this->segmentPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $lockedSegment );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );
        $this->assertTrue( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndSegmentPolicyEnabled_WhenPassingAdditionalUnsetSegment_ThenExpectActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );

        $lockedSegment1 = new SegmentStub( 1, 10 );
        $lockedSegment2 = new SegmentStub( 2, 15 );
        $this->segmentPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $lockedSegment1 );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment1, $lockedSegment2 ] );
        $this->assertTrue( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndSegmentPolicyDisabled_ThenExpectInActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );

        $lockedSegment = new SegmentStub( 1, 10 );
        $this->segmentPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $lockedSegment );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );
        $this->assertFalse( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndSegmentPolicyDisabledAndUserAutoSubscribed_ThenExpectInActive()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );
        $this->autoSubscribersGatewayMock->setUserSubscriberStatus( $this->currentUser, true );

        $lockedSegment = new SegmentStub( 1, 10 );
        $this->segmentPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $lockedSegment );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment ] );
        $this->assertFalse( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndMultipleNonLockedSegmentPoliciesAreSet_ThenSegmentPrioritiesDecides()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );

        $lockedSegment1 = new SegmentStub( 1, 10 );
        $lockedSegment2 = new SegmentStub( 2, 100 );
        $this->segmentPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $lockedSegment1 );
        $this->segmentPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $lockedSegment2 );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment1, $lockedSegment2 ] );
        $this->assertTrue( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleVisibleAndMultipleNonLockedSegmentPoliciesAreSetWithSamePriority_ThenOneOfThemDecide()
    {
        $this->toggleGatewayMock->setVisibility( self::TEST_TOGGLE, true );

        $lockedSegment1 = new SegmentStub( 1, 10 );
        $lockedSegment2 = new SegmentStub( 2, 10 );
        $this->segmentPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $lockedSegment1 );
        $this->segmentPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $lockedSegment2 );

        $currentUserToggleChecker = $this->createCurrentToggleCheckerWithSegments( [ $lockedSegment1, $lockedSegment2 ] );
        $this->assertTrue( $currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }
}
