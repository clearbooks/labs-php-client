<?php
namespace Clearbooks\Labs\Client\Toggle\Entity;

interface Segment extends Identity
{
    /**
     * @return int
     */
    public function getPriority();

    /**
     * @return bool
     */
    public function isLocked();
}
