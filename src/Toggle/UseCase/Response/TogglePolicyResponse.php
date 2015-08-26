<?php
namespace Clearbooks\Labs\Client\Toggle\UseCase\Response;

interface TogglePolicyResponse
{
    /**
     * @return bool
     */
    public function isEnabled();
    /**
     * @return bool
     */
    public function isNotSet();
}
//EOF TogglePolicyResponse.php