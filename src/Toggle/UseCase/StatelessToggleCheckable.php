<?php
namespace Clearbooks\Labs\Client\Toggle\UseCase;

use Clearbooks\Labs\Client\Toggle\Entity\Group;
use Clearbooks\Labs\Client\Toggle\Entity\User;

interface StatelessToggleCheckable
{
    /**
     * @param string $toggleName
     * @param User $user
     * @param Group $group
     * @return bool is it active
     */
    public function isToggleActive($toggleName, User $user, Group $group);
}