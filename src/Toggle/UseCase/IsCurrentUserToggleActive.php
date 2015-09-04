<?php
namespace Clearbooks\Labs\Client\Toggle\UseCase;

interface IsCurrentUserToggleActive
{
    /**
     * @param string $toggleName
     * @return bool is it active
     */
    public function isToggleActive( $toggleName );
}
//EOF IsCurrentUserToggleActive.php