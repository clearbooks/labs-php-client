<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

interface ToggleGateway
{
    /**
     * @param string $toggleName
     * @return bool
     */
    public function isToggleVisibleForUsers($toggleName);
}
//EOF ToggleGateway.php