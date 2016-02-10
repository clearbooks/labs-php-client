<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

class ToggleGatewayMock implements ToggleGateway
{
    /**
     * @var bool[]
     */
    private $visibility = [ ];

    /**
     * @var bool[]
     */
    private $isGroupToggle = [ ];

    /**
     * @var bool[]
     */
    private $isReleaseDateTodayOrInThePast = [ ];

    /**
     * @param string $toggleName
     * @return bool
     */
    public function isToggleVisibleForUsers( $toggleName )
    {
        return isset( $this->visibility[$toggleName] ) && $this->visibility[$toggleName];
    }

    /**
     * @param string $toggleName
     * @return bool
     */
    public function isGroupToggle( $toggleName )
    {
        return isset( $this->isGroupToggle[$toggleName] ) && $this->isGroupToggle[$toggleName];
    }

    /**
     * @param string $toggleName
     * @return bool
     */
    public function isReleaseDateOfToggleReleaseTodayOrInThePast( $toggleName )
    {
        return isset( $this->isReleaseDateTodayOrInThePast[$toggleName] ) && $this->isReleaseDateTodayOrInThePast[$toggleName];
    }

    /**
     * @param string $toggleName
     * @param boolean $visibility
     */
    public function setVisibility( $toggleName, $visibility )
    {
        $this->visibility[$toggleName] = $visibility;
    }

    /**
     * @param string $toggleName
     * @param boolean $isGroupToggle
     */
    public function setIsGroupToggle( $toggleName, $isGroupToggle )
    {
        $this->isGroupToggle[$toggleName] = $isGroupToggle;
    }

    /**
     * @param string $toggleName
     * @param boolean $isReleaseDateTodayOrInThePast
     */
    public function setIsReleaseDateTodayOrInThePast( $toggleName, $isReleaseDateTodayOrInThePast )
    {
        $this->isReleaseDateTodayOrInThePast[$toggleName] = $isReleaseDateTodayOrInThePast;
    }
}
