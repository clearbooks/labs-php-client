<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

class ToggleGatewayMock extends BaseToggleMock implements ToggleGateway
{
    /** @var bool */
    private $visibility;
    /** @var string */
    private $type;

    /**
     * ToggleGatewayMock constructor.
     * @param string $toggleName
     * @param bool $visibility
     * @param string $type
     */
    public function __construct($toggleName, $visibility, $type)
    {
        parent::__construct($toggleName);
        $this->visibility = $visibility;
        $this->type = $type;
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
     * @return string
     */
    public function getType($toggleName)
    {
        return $this->type;
    }
}
//EOF ToggleGatewayMock.php