<?php
namespace Clearbooks\Labs\Client\Toggle;

use Clearbooks\Labs\Client\Toggle\Entity\GroupStub;
use Clearbooks\Labs\Client\Toggle\Entity\UserStub;
use Clearbooks\Labs\Client\Toggle\Gateway\BaseTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\GroupTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\ToggleGatewayMock;
use Clearbooks\Labs\Client\Toggle\Gateway\UserTogglePolicyGatewayMock;
use Clearbooks\Labs\Client\Toggle\UseCase\IsCurrentUserToggleActive;

class IsCurrentUserToggleActiveTest extends \PHPUnit_Framework_TestCase
{

    const INVISIBLE_FEATURE_TOGGLE = 'Invisible Feature toggle';
    const VISIBLE_FEATURE_TOGGLE = 'Visible Feature Toggle';
    const DISABLED_GROUP = 'Disabled Group';
    const DISABLED_USER = 'Disabled user';
    const NOT_SET_GROUP_POLICY = 'Not set group policy';
    const NOT_SET_USER_POLICY = 'Not set user policy';
    const ENABLED_GROUP = 'Enabled group';
    const ENABLED_USER = 'Enabled user';

    /** @var BaseTogglePolicyGatewayMock */
    private $groupPolicy;
    /** @var BaseTogglePolicyGatewayMock */
    private $userPolicy;
    /** @var ToggleGatewayMock */
    private $toggleGateway;
    /** @var IsCurrentUserToggleActive */
    private $checker;

    public function testWhenToggleNotVisible_ThenInactive()
    {
        $this->setupChecker(false);
        $this->assertFalse($this->checker->isToggleActive(self::INVISIBLE_FEATURE_TOGGLE));
        $this->assertTrue($this->toggleGateway->isCalledProperly());
    }


    public function testWhenToggleVisibleAndGroupNotSetAndUserNotSet_ThenInactive()
    {
        $this->setupChecker(true);
        $this->assertFalse($this->checker->isToggleActive(self::VISIBLE_FEATURE_TOGGLE));
        $this->assertTrue($this->groupPolicy->isCalledProperly());
        $this->assertTrue($this->userPolicy->isCalledProperly());
    }

    public function testWhenToggleVisibleAndGroupDisabled_ThenInactive()
    {
        $this->setupChecker(true,true,false,true,true);
        $this->assertFalse($this->checker->isToggleActive(self::VISIBLE_FEATURE_TOGGLE));
        $this->assertTrue($this->groupPolicy->isCalledProperly());
    }

    public function testWhenToggleVisibleAndGroupEnabled_ThenActive()
    {
        $this->setupChecker(true,true,true,true,false);
        $this->assertTrue($this->checker->isToggleActive(self::VISIBLE_FEATURE_TOGGLE));
        $this->assertTrue($this->groupPolicy->isCalledProperly());
    }


    public function testWhenToggleVisibleAndGroupNotSetAndUserDisabled_ThenInactive()
    {
        $this->setupChecker(true, false, false, true);
        $this->assertFalse($this->checker->isToggleActive(self::VISIBLE_FEATURE_TOGGLE));
        $this->assertTrue($this->groupPolicy->isCalledProperly());
        $this->assertTrue($this->userPolicy->isCalledProperly());
    }

    public function testWhenToggleVisibleAndGroupNotSetAndUserEnabled_ThenActive()
    {
        $this->setupChecker(true, false, false, true, true);
        $this->assertTrue($this->checker->isToggleActive(self::VISIBLE_FEATURE_TOGGLE));
        $this->assertTrue($this->groupPolicy->isCalledProperly());
        $this->assertTrue($this->userPolicy->isCalledProperly());
    }

    public function testWhenToggleVisibleAndTypeIsGroupAndGroupNotSetAndUserEnabled_ThenInactive()
    {
        $this->setupChecker(true, false, false, true, true, true);
        $this->assertFalse($this->checker->isToggleActive(self::VISIBLE_FEATURE_TOGGLE));
        $this->assertTrue($this->groupPolicy->isCalledProperly());
        $this->assertFalse($this->userPolicy->isCalledProperly());
    }

    public function testWhenToggleVisibleAndTypeIsGroupAndGroupDisabledAndUserEnabled_ThenInactive()
    {
        $this->setupChecker(true, true, false, true, true, true);
        $this->assertFalse($this->checker->isToggleActive(self::VISIBLE_FEATURE_TOGGLE));
        $this->assertTrue($this->groupPolicy->isCalledProperly());
        $this->assertFalse($this->userPolicy->isCalledProperly());
    }


    public function testWhenToggleVisibleAndTypeIsGroupAndGroupEnabledAndUserEnabled_ThenActive()
    {
        $this->setupChecker(true, true, true, true, true, true);
        $this->assertTrue($this->checker->isToggleActive(self::VISIBLE_FEATURE_TOGGLE));
        $this->assertTrue($this->groupPolicy->isCalledProperly());
        $this->assertFalse($this->userPolicy->isCalledProperly());
    }

    /**
     * @test
     */
    public function WhenToggleVisibleAndNotEnabledByPolicyButReleaseDateIsTodayOrInThePast_ThenActive()
    {
        $this->setupChecker( true, false, false, false, false, false, true );
        $this->assertTrue( $this->checker->isToggleActive( self::VISIBLE_FEATURE_TOGGLE ) );
    }

    /**
     * @test
     */
    public function WhenToggleInvisibleAndNotEnabledByPolicyButReleaseDateIsTodayOrInThePast_ThenInactive()
    {
        $this->setupChecker( false, false, false, false, false, false, true );
        $this->assertFalse( $this->checker->isToggleActive( self::INVISIBLE_FEATURE_TOGGLE ) );
    }

    /**
     * @param bool $isToggleVisible
     * @param bool $isGroupSet
     * @param bool $isGroupEnabled
     * @param bool $isUserSet
     * @param bool $isUserEnabled
     * @param bool $isGroupType
     * @param bool $isReleaseDateTodayOrInThePast
     * @return string
     */
    private function setupChecker( $isToggleVisible, $isGroupSet = false, $isGroupEnabled = false, $isUserSet = false,
                                   $isUserEnabled = false, $isGroupType = false,
                                   $isReleaseDateTodayOrInThePast = false )
    {
        $toggleId = $isToggleVisible ? self::VISIBLE_FEATURE_TOGGLE : self::INVISIBLE_FEATURE_TOGGLE;
        $groupId = $isGroupSet ? self::NOT_SET_GROUP_POLICY : ($isGroupEnabled ? self::ENABLED_GROUP : self::DISABLED_GROUP);
        $userId = $isUserSet ? self::NOT_SET_USER_POLICY : ($isUserEnabled ? self::ENABLED_USER : self::DISABLED_USER);
        $group = new GroupStub($groupId);
        $user = new UserStub($userId);
        $this->toggleGateway = new ToggleGatewayMock( $toggleId, $isToggleVisible, $isGroupType, $isReleaseDateTodayOrInThePast );
        $this->groupPolicy = new GroupTogglePolicyGatewayMock($isToggleVisible, $group, new TogglePolicyResponseStub($isGroupSet, $isGroupEnabled));
        $this->userPolicy = new UserTogglePolicyGatewayMock($isToggleVisible, $user, new TogglePolicyResponseStub($isUserSet, $isUserEnabled));
        $ToggleChecker = new StatelessToggleChecker($this->toggleGateway, $this->userPolicy, $this->groupPolicy);
        $this->checker = new CurrentUserToggleChecker($user, $group, $ToggleChecker);
        return $toggleId;
    }
}
//EOF IsCurrentUserToggleActiveTest.php
