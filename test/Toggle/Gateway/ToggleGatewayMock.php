<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

class ToggleGatewayMock extends BaseToggleMock implements ToggleGateway
{
    /** @var bool */
    private $visibility;

    /**
     * ToggleGatewayMock constructor.
     * @param string $toggleName
     * @param bool $visibility
     */
    public function __construct($toggleName, $visibility)
    {
        parent::__construct($toggleName);
        $this->visibility = $visibility;
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
}
//EOF ToggleGatewayMock.php