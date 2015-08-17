<?php
namespace Clearbooks\LabsClient\Toggle\Gateway;

interface ToggleGateway
{
    /**
     * @param $toggleId
     * @return bool
     */
    public function isToggleVisibleForUsers($toggleId);
}
//EOF ToggleGateway.php