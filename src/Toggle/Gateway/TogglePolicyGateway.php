<?php
namespace Clearbooks\LabsPhpClient\Toggle\Gateway;

use Clearbooks\LabsPhpClient\Toggle\Entity\Identity;
use Clearbooks\LabsPhpClient\Toggle\UseCase\Response\TogglePolicyResponse;

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