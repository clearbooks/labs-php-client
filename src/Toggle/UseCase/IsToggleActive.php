<?php
namespace Clearbooks\LabsClient\Toggle\UseCase;

interface IsToggleActive
{
    /**
     * @param string $toggleName
     * @return bool is it active
     */
    public function isToggleActive( $toggleName );
}
//EOF IsToggleActive.php