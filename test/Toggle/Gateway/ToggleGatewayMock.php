<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

class ToggleGatewayMock extends BaseToggleMock implements ToggleGateway
{
    /** @var bool */
    private $visibility;

    /** @var bool */
    private $isGroupToggle;

    /** @var bool */
    private $isReleaseDateTodayOrInThePast;

    /**
     * ToggleGatewayMock constructor.
     * @param string $toggleName
     * @param bool $visibility
     * @param bool $isGroupToggle
     * @param bool $isReleaseDateTodayOrInThePast
     */
    public function __construct( $toggleName, $visibility, $isGroupToggle, $isReleaseDateTodayOrInThePast )
    {
        parent::__construct($toggleName);
        $this->visibility = $visibility;
        $this->isGroupToggle = $isGroupToggle;
        $this->isReleaseDateTodayOrInThePast = $isReleaseDateTodayOrInThePast;
    }

    /**
     * @param string $toggleName
     * @return bool
     */
    public function isToggleVisibleForUsers($toggleName)
    {
        $this->testToggle($toggleName);
        return $this->visibility;
    }

    /**
     * @param string $toggleName
     * @return bool
     */
    public function isGroupToggle($toggleName)
    {
        return $this->isGroupToggle;
    }

    /**
     * @param string $toggleName
     * @return bool
     */
    public function isReleaseDateOfToggleReleaseTodayOrInThePast( $toggleName )
    {
        return $this->isReleaseDateTodayOrInThePast;
    }
}
//EOF ToggleGatewayMock.php
