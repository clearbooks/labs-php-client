<?php
namespace Clearbooks\Labs\Client\Toggle;

class TogglePolicyResponseStub implements UseCase\Response\TogglePolicyResponse
{
    /**
     * @var bool|null
     */
    private $enabled = null;

    /**
     * TogglePolicyResponseStub constructor.
     * @param bool|null $enabled
     */
    public function __construct( $enabled = null )
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @return bool
     */
    public function isNotSet()
    {
        return $this->enabled === null;
    }
}
//EOF TogglePolicyResponseStub.php
