<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

use Clearbooks\Labs\Client\Toggle\Entity\Identity;

interface AutoSubscribersGateway
{
    /**
     * @param Identity $user
     * @return bool
     */
    public function isUserAutoSubscriber( Identity $user );
}
