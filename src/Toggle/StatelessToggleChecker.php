<?php
namespace Clearbooks\Labs\Client\Toggle;

use Clearbooks\Labs\Client\Toggle\Entity\Group;
use Clearbooks\Labs\Client\Toggle\Entity\User;
use Clearbooks\Labs\Client\Toggle\Gateway\GroupTogglePolicyGateway;
use Clearbooks\Labs\Client\Toggle\Gateway\ToggleGateway;
use Clearbooks\Labs\Client\Toggle\Gateway\TogglePolicyGateway;
use Clearbooks\Labs\Client\Toggle\Gateway\UserTogglePolicyGateway;
use Clearbooks\Labs\Client\Toggle\UseCase\Response\TogglePolicyResponse;

class StatelessToggleChecker implements UseCase\ToggleChecker
{
    /** @var ToggleGateway */
    private $toggleGateway;
    /** @var TogglePolicyGateway */
    private $groupPolicy;
    /** @var TogglePolicyGateway */
    private $userPolicy;

    /**
     * @param ToggleGateway $toggleGateway
     * @param UserTogglePolicyGateway $userPolicy
     * @param GroupTogglePolicyGateway $groupPolicy
     */
    public function __construct(ToggleGateway $toggleGateway ,UserTogglePolicyGateway $userPolicy ,GroupTogglePolicyGateway $groupPolicy)
    {
        $this->toggleGateway = $toggleGateway;
        $this->groupPolicy = $groupPolicy;
        $this->userPolicy = $userPolicy;
    }

    /**
     * @param string $toggleName
     * @param User $user
     * @param Group $group
     * @return bool is it active
     */
    public function isToggleActive( $toggleName, User $user, Group $group )
    {
        if ( !$this->toggleGateway->isToggleVisibleForUsers($toggleName) ) {
            return false;
        }
        $groupPolicyResponse = $this->groupPolicy->getTogglePolicy($toggleName, $group);
        if ( $groupPolicyResponse->isEnabled() ) {
            return true;
        }
        if ( $this->isGroupToggleInUnsetGroup($groupPolicyResponse,$this->toggleGateway->isGroupToggle($toggleName)) ) {
            return false;
        }
        return $this->ifGroupUnsetUseUserPolicy($toggleName, $groupPolicyResponse, $user);
    }

    /**
     * @param UseCase\Response\TogglePolicyResponse $groupPolicyResponse
     * @param bool $isGroupToggle
     * @return bool
     */
    private function isGroupToggleInUnsetGroup(UseCase\Response\TogglePolicyResponse $groupPolicyResponse, $isGroupToggle)
    {
        return $isGroupToggle && $groupPolicyResponse->isNotSet() ;
    }

    /**
     * @param $toggleName
     * @param TogglePolicyResponse $groupPolicyResponse
     * @param User $user
     * @return bool
     */
    private function ifGroupUnsetUseUserPolicy($toggleName, $groupPolicyResponse, User $user)
    {
        return $groupPolicyResponse->isNotSet() && $this->userPolicy->getTogglePolicy($toggleName, $user)->isEnabled();
    }
}