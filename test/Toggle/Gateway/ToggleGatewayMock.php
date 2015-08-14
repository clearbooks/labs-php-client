<?php
namespace Clearbooks\LabsClient\Toggle\Gateway;

class ToggleGatewayMock extends BaseToggleMock implements ToggleGateway
{
    /** @var bool */
    private $visibility;

    /**
     * ToggleGatewayMock constructor.
     * @param string $toggleId
     * @param bool $visibility
     */
    public function __construct($toggleId, $visibility)
    {
        parent::__construct($toggleId);
        $this->visibility = $visibility;
    }

    /**
     * @param $toggleId
     * @return bool
     */
    public function isToggleVisibleForUsers($toggleId)
    {
        $this->testToggle($toggleId);
        return $this->visibility;
    }
}
//EOF ToggleGatewayMock.php