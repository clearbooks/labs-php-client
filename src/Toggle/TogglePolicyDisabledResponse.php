<?php
namespace Clearbooks\Labs\Client\Toggle;

class TogglePolicyDisabledResponse implements UseCase\Response\TogglePolicyResponse
{
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return false;
    }

    /**
     * @return bool
     */
    public function isNotSet()
    {
        return false;
    }
}
//EOF TogglePolicyResponse.php