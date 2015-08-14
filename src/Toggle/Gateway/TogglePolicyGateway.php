<?php
namespace Clearbooks\LabsClient\Toggle\Gateway;

use Clearbooks\LabsClient\Toggle\Entity\Identity;
use Clearbooks\LabsClient\Toggle\UseCase\Response\TogglePolicyResponse;

interface TogglePolicyGateway
{
    /**
     * @param string $toggleId
     * @param Identity $idHolder
     * @return TogglePolicyResponse
     */
    public function getTogglePolicy($toggleId, Identity $idHolder);
}
//EOF TogglePolicyGateway.php