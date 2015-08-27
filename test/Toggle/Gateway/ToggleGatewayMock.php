<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

class ToggleGatewayMock extends BaseToggleMock implements ToggleGateway
{
    /** @var bool */
    private $visibility;
    /** @var bool */
    private $isGroupToggle;

    /**
     * ToggleGatewayMock constructor.
     * @param string $toggleName
     * @param bool $visibility
     * @param bool $isGroupToggle
     */
    public function __construct($toggleName, $visibility, $isGroupToggle)
    {
        parent::__construct($toggleName);
        $this->visibility = $visibility;
        $this->isGroupToggle = $isGroupToggle;
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
}
//EOF ToggleGatewayMock.php