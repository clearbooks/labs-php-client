<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

interface ToggleGateway
{
    /**
     * @param string $toggleName
     * @return bool
     */
    public function isToggleVisibleForUsers($toggleName);

    /**
     * @param string $toggleName
     * @return bool
     */
    public function isGroupToggle($toggleName);
}
//EOF ToggleGateway.php