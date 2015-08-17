<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

interface ToggleGateway
{
    /**
     * @param $toggleId
     * @return bool
     */
    public function isToggleVisibleForUsers($toggleId);
}
//EOF ToggleGateway.php