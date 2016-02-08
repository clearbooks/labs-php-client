<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

use Clearbooks\Labs\Client\Toggle\Entity\Identity;

class AutoSubscribersGatewayMock implements AutoSubscribersGateway
{
    private $subscribers = [ ];

    /**
     * @param Identity $user
     * @return bool
     */
    public function isUserAutoSubscriber( Identity $user )
    {
        return isset( $this->subscribers[$user->getId()] ) && $this->subscribers[$user->getId()];
    }

    /**
     * @param Identity $user
     * @param bool $isSubscriber
     */
    public function setUserSubscriberStatus( Identity $user, $isSubscriber )
    {
        $this->subscribers[$user->getId()] = $isSubscriber;
    }
}
