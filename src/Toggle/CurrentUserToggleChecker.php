<?php
namespace Clearbooks\Labs\Client\Toggle;

use Clearbooks\Labs\Client\Toggle\Entity\Group;
use Clearbooks\Labs\Client\Toggle\Entity\User;
use Clearbooks\Labs\Client\Toggle\UseCase\IsCurrentUserToggleActive;
use Clearbooks\Labs\Client\Toggle\UseCase\ToggleChecker;

class CurrentUserToggleChecker implements IsCurrentUserToggleActive
{
    /**
     * @var Group
     */
    private $group;

    /**
     * @var User
     */
    private $user;

    /**
     * @var ToggleChecker
     */
    private $toggleChecker;

    /**
     * @param ToggleChecker $toggleChecker
     */
    public function __construct( ToggleChecker $toggleChecker )
    {
        $this->toggleChecker = $toggleChecker;
    }

    /**
     * @param string $toggleName
     * @return bool is it active
     */
    public function isToggleActive( $toggleName )
    {
        return $this->toggleChecker->isToggleActive( $toggleName, $this->user, $this->group );
    }

    /**
     * @param Group $group
     */
    public function setGroup( $group )
    {
        $this->group = $group;
    }

    /**
     * @param User $user
     */
    public function setUser( $user )
    {
        $this->user = $user;
    }
}
