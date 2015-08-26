<?php
namespace Clearbooks\Labs\Client\Toggle;

class TogglePolicyResponse implements UseCase\Response\TogglePolicyResponse
{
    /** @var bool */
    private $isSet;
    /** @var bool */
    private $enabled;

    /**
     * TogglePolicyResponse constructor.
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
//EOF TogglePolicyResponse.php