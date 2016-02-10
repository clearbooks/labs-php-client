<?php
namespace Clearbooks\Labs\Client\Toggle\Gateway;

use Clearbooks\Labs\Client\Toggle\Entity\Identity;
use Clearbooks\Labs\Client\Toggle\UseCase\Response\TogglePolicyResponse;

interface TogglePolicyGateway
{
    /**
     * @param string $toggleName
     * @param Identity $idHolder
     * @return TogglePolicyResponse
     */
    public function getTogglePolicy( $toggleName, Identity $idHolder );
}
//EOF TogglePolicyGateway.php
