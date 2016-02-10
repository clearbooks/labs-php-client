<?php
namespace Clearbooks\Labs\Client\Toggle;

use Clearbooks\Labs\Client\Toggle\Entity\GroupStub;
use Clearbooks\Labs\Client\Toggle\Entity\Identity;
use Clearbooks\Labs\Client\Toggle\Entity\UserStub;
use Clearbooks\Labs\Client\Toggle\Gateway\AutoSubscribersGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\BaseTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\GroupTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\ToggleGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\UserTogglePolicyGatewayMock;
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
     * @var IsCurrentUserToggleActive
     */
    private $currentUserToggleChecker;

    /**
     * @var Identity
     */
    private $currentUser;

    /**
     * @var Identity
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
        $statelessToggleChecker = new StatelessToggleChecker(
                $this->toggleGatewayMock, $this->userPolicyGatewayMock,
                $this->groupPolicyGatewayMock, $this->autoSubscribersGatewayMock
        );

        $this->currentUserToggleChecker = new CurrentUserToggleChecker( $statelessToggleChecker );
        $this->currentUserToggleChecker->setUser( $this->currentUser );
        $this->currentUserToggleChecker->setGroup( $this->currentGroup );
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
    public function GivenGroupToggleNotSetByPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectInactive()
    {
        $this->toggleGatewayMock->setIsGroupToggle( self::TEST_TOGGLE, true );
        $this->autoSubscribersGatewayMock->setUserSubscriberStatus( $this->currentUser, true );
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleDisabledByUserPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectInactive()
    {
        $this->userPolicyGatewayMock->setTogglePolicyDisabled( self::TEST_TOGGLE, $this->currentUser );
        $this->autoSubscribersGatewayMock->setUserSubscriberStatus( $this->currentUser, true );
        $this->assertFalse( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleEnabledByUserPolicyAndReleaseDateIsInTheFutureAndUserIsNotAutoSubscribed_ThenExpectActive()
    {
        $this->userPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentUser );
        $this->assertTrue( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleNotSetByUserPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectActive()
    {
        $this->autoSubscribersGatewayMock->setUserSubscriberStatus( $this->currentUser, true );
        $this->assertTrue( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }

    /**
     * @test
     */
    public function GivenToggleEnabledByUserPolicyAndReleaseDateIsInTheFutureAndUserIsAutoSubscribed_ThenExpectActive()
    {
        $this->userPolicyGatewayMock->setTogglePolicyEnabled( self::TEST_TOGGLE, $this->currentUser );
        $this->autoSubscribersGatewayMock->setUserSubscriberStatus( $this->currentUser, true );
        $this->assertTrue( $this->currentUserToggleChecker->isToggleActive( self::TEST_TOGGLE ) );
    }
}
