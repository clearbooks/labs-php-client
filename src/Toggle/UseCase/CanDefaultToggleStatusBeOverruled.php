<?php
namespace Clearbooks\Labs\Client\Toggle\UseCase;

use Clearbooks\Labs\Client\Toggle\Entity\Segment;

interface CanDefaultToggleStatusBeOverruled
{
    /**
     * @param string $toggleName
     * @param Segment[] $segments
     * @return bool
     */
    public function canBeOverruled( $toggleName, array $segments );
}
