<?php
namespace Clearbooks\Labs\Client\Toggle;

use Clearbooks\Labs\Client\Toggle\Entity\Group;
use Clearbooks\Labs\Client\Toggle\Entity\User;
use Clearbooks\Labs\Client\Toggle\Gateway\TogglePolicyGateway;
use Clearbooks\Labs\Client\Toggle\UseCase\IsToggleActive;
use Clearbooks\Labs\Client\Toggle\Gateway\ToggleGateway;

class ToggleChecker implements IsToggleActive
{
    /** @var ToggleGateway */
    private $toggleGateway;
    /** @var TogglePolicyGateway */
    private $groupPolicy;
    /** @var TogglePolicyGateway */
    private $userPolicy;
    /** @var Group */
    private $group;
    /** @var User */
    private $user;

    /**
     * @param User $user
     * @param Group $group
     * @param ToggleGateway $toggleGateway
     * @param TogglePolicyGateway $userPolicy
     * @param TogglePolicyGateway $groupPolicy
     */
    public function __construct(
        User $user,
        Group $group,
        ToggleGateway $toggleGateway ,
        TogglePolicyGateway $userPolicy,
        TogglePolicyGateway $groupPolicy
    )
    {
        $this->toggleGateway = $toggleGateway;
        $this->groupPolicy = $groupPolicy;
        $this->group = $group;
        $this->user = $user;
        $this->userPolicy = $userPolicy;
    }

    /**
     * @param string $toggleName
     * @return bool is it active
     */
    public function isToggleActive( $toggleName )
    {
        if ( !$this->toggleGateway->isToggleVisibleForUsers($toggleName) ) {
            return false;
        }
        $groupPolicyResponse = $this->groupPolicy->getTogglePolicy($toggleName, $this->group);
        return $groupPolicyResponse->isEnabled() ||
        ( $groupPolicyResponse->isNotSet() && $this->userPolicy->getTogglePolicy($toggleName, $this->user)->isEnabled());
    }
}
//EOF ToggleChecker.php