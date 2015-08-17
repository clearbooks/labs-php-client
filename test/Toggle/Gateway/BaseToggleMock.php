<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

class BaseToggleMock
{
    /** @var string */
    private $toggleId;
    /** @var bool */
    private $calledProperly = false;

    /**
     * @param string $toggleId
     */
    public function __construct($toggleId)
    {
        $this->toggleId = $toggleId;
    }

    /**
     * @param string $toggleId
     */
    protected function testToggle($toggleId) {
        $this->calledProperly = $toggleId == $this->toggleId;
    }

    /**
     * @return bool
     */
    public function isCalledProperly()
    {
        return $this->calledProperly;
    }

}
//EOF BaseToggleMock.php