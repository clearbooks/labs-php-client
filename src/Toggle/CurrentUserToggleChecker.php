<?php
namespace Clearbooks\Labs\Client\Toggle;

use Clearbooks\Labs\Client\Toggle\Entity\Group;
use Clearbooks\Labs\Client\Toggle\Entity\Segment;
use Clearbooks\Labs\Client\Toggle\Entity\User;
use Clearbooks\Labs\Client\Toggle\UseCase\IsCurrentUserToggleActive;
use Clearbooks\Labs\Client\Toggle\UseCase\ToggleChecker;

class CurrentUserToggleChecker implements IsCurrentUserToggleActive
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var Group
     */
    private $group;

    /**
     * @var Segment[]
     */
    private $segments;

    /**
     * @var ToggleChecker
     */
    private $toggleChecker;

    /**
     * @param User $user
     * @param Group $group
     * @param Segment[] $segments
     * @param ToggleChecker $toggleChecker
     */
    public function __construct( User $user, Group $group, array $segments, ToggleChecker $toggleChecker )
    {
        $this->user = $user;
        $this->group = $group;
        $this->segments = $segments;
        $this->toggleChecker = $toggleChecker;
    }

    /**
     * @param string $toggleName
     * @return bool is it active
     */
    public function isToggleActive( $toggleName )
    {
        return $this->toggleChecker->isToggleActive( $toggleName, $this->user, $this->group, $this->segments );
    }
}
