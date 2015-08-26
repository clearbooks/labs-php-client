<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

class BaseToggleMock
{
    /** @var string */
    private $toggleName;
    /** @var bool */
    private $calledProperly = false;

    /**
     * @param string $toggleName
     */
    public function __construct($toggleName)
    {
        $this->toggleName = $toggleName;
    }

    /**
     * @param string $toggleName
     */
    protected function testToggle($toggleName) {
        $this->calledProperly = $toggleName == $this->toggleName;
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