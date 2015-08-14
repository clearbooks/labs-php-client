<?php
namespace Clearbooks\LabsPhpClient\Toggle;

use Clearbooks\LabsPhpClient\Toggle\UseCase\Response\TogglePolicyResponse;

class TogglePolicyResponseStub implements TogglePolicyResponse
{
    /** @var bool */
    private $isSet;
    /** @var bool */
    private $enabled;

    /**
     * TogglePolicyResponseStub constructor.
     * @param bool $isSet
     * @param bool $enabled
     */
    public function __construct($isSet, $enabled)
    {
        $this->isSet = $isSet;
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return $this->isSet && !$this->enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isSet && $this->enabled;
    }

    /**
     * @return bool
     */
    public function isNotSet()
    {
        return !$this->isSet;
    }
}
//EOF TogglePolicyResponseStub.php